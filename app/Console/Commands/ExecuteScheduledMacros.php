<?php

namespace App\Console\Commands;

use App\Constants\MacroConstant;
use App\Constants\ShopConstant;
use App\Models\MacroConfiguration;
use App\Repositories\Contracts\AlertRepository;
use App\Repositories\Contracts\LinkedUserInfoRepository;
use App\Repositories\Contracts\PolicyRepository;
use App\Repositories\Contracts\TaskRepository;
use App\WebServices\OSS\ShopService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ExecuteScheduledMacros extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:execute-scheduled-macros';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check ready macros and execute them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->checkAndUpdateMacro();

        $macros = MacroConfiguration::where('status', MacroConstant::MACRO_STATUS_READY)
            ->whereIn('macro_type', MacroConstant::MACRO_SCHEDULABLE_TYPES)
            ->get();

        if ($macros->isEmpty()) {
            return Command::SUCCESS;
        }

        $oneTime = [];
        $cycle = [];

        foreach ($macros as $macro) {
            if ($macro->isOneTime()) {
                $oneTime[] = $macros->shift();
            } else {
                $cycle[] = $macros->shift();
            }
        }

        $this->handleMacroOneTime($oneTime);
        $this->handleCyclicalMacros($cycle);

        return Command::SUCCESS;
    }

    private function checkAndUpdateMacro()
    {
        MacroConfiguration::where('status', MacroConstant::MACRO_STATUS_NOT_READY)
            ->whereIn('macro_type', MacroConstant::MACRO_SCHEDULABLE_TYPES)
            ->whereRaw("JSON_EXTRACT(time_conditions, '$.applicable_date') <= ?", [now()->format('Y-m-d H:i:s')])
            ->update([
                'status' => MacroConstant::MACRO_STATUS_READY,
            ]);
    }

    private function handleMacroOneTime(array $macros)
    {
        $now = now();

        foreach ($macros as $macro) {
            $timeCondition = Carbon::create($macro->time_condition_designation);

            if (
                $timeCondition->year == $now->year
                && $timeCondition->month == $now->month
                && $timeCondition->day == $now->day
            ) {
                $this->executeByMacroType($macro);
                $macro->status = MacroConstant::MACRO_STATUS_FINISH;
                $macro->save();
            }
        }
    }

    private function handleCyclicalMacros(array $macros)
    {
        foreach ($macros as $macro) {
            $cronExpression = $macro->cron_expression;

            if ($cronExpression->isDue()) {
                $this->executeByMacroType($macro);
            }
        }
    }

    private function executeByMacroType(MacroConfiguration $macro): void
    {
        switch ($macro->macro_type) {
            case MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION:
                $this->createSimulationPolicy($macro);
                break;
            case MacroConstant::MACRO_TYPE_POLICY_REGISTRATION:
                $this->createPolicy($macro);
                break;
            case MacroConstant::MACRO_TYPE_TASK_ISSUE:
                $this->createTask($macro);
                break;
            case MacroConstant::MACRO_TYPE_ALERT_DISPLAY:
                $this->createAlert($macro);
                break;
            default:
                break;
        }

        $this->info('"'.$macro->name.'" is executed.');
    }

    private function createSimulationPolicy(MacroConfiguration $macro): void
    {
        $templates = $macro->simulationTemplates;
        $diff = now()->diffInMinutes($macro->cron_expression->getNextRunDate());

        foreach ($templates as $template) {
            $data = $template->payload_decode;

            foreach ($this->getListStoreId($macro) as $storeId) {
                logger()->channel('macro')->info('Run create simulation: '.json_encode($data + [
                    'store_id' => $storeId,
                ]));

                $this->policyRepository()->createSimulation($data, $storeId);

                // Increase the start and end times for the next new creation.
                $template->payload = $this->getDataWithDateTimeForNextCreation(
                    $data,
                    'simulation_start_date',
                    'simulation_start_time',
                    'simulation_end_date',
                    'simulation_end_time',
                    $diff,
                );
                $template->save();
            }
        }
    }

    private function createPolicy(MacroConfiguration $macro): void
    {
        $templates = $macro->policyTemplates;
        $diff = now()->diffInMinutes($macro->cron_expression->getNextRunDate());

        foreach ($templates as $template) {
            $data = $template->payload_decode;

            foreach ($this->getListStoreId($macro) as $index => $storeId) {
                $data = array_merge($data, ['store_id' => $storeId]);
                logger()->channel('macro')->info('Run create policy: '.json_encode($data));

                $this->policyRepository()->create(
                    $this->policyRepository()->handleValidation($data, $index),
                    $storeId
                );

                // Increase the start and end times for the next new creation.
                $template->payload = $this->getDataWithDateTimeForNextCreation(
                    $data,
                    'execution_date',
                    'execution_time',
                    'undo_date',
                    'undo_time',
                    $diff,
                );
                $template->save();
            }
        }
    }

    private function createTask(MacroConfiguration $macro): void
    {
        $templates = $macro->taskTemplates;
        $diff = now()->diffInMinutes($macro->cron_expression->getNextRunDate());
        $storeIds = $macro->store_ids;

        foreach ($templates as $template) {
            $data = $template->payload_decode;

            if (str($storeIds)->contains(ShopConstant::SHOP_OWNER_OPTION)) {
                $storeIds = (string) str($storeIds)->replace(
                    ShopConstant::SHOP_OWNER_OPTION,
                    '__shop_owner_'.app(LinkedUserInfoRepository::class)->getOssUserIdByCssUserId($macro->created_by),
                );
            }

            logger()->channel('macro')->info('Run create task for multiple shop: '.json_encode($data + ['store_id' => $storeIds]));

            $result = $this->taskRepository()->createForMultipleShops($data, $storeIds);

            if (! $result || $result->get('errors')) {
                $content = is_null($result) ? 'check the OSS log' : json_encode($result->get('errors'));
                logger()->channel('macro')->error('Create task failed: '.$content);
            }

            // Increase the start and end times for the next new creation.
            $template->payload = $this->getDataWithDateTimeForNextCreation(
                $data,
                'start_date',
                'start_time',
                'due_date',
                'due_time',
                $diff,
            );
            $template->save();
        }
    }

    private function createAlert(MacroConfiguration $macro): void
    {
        $templates = $macro->alertTemplates;

        foreach ($templates as $template) {
            $data = $template->payload_decode;

            foreach ($this->getListStoreId($macro) as $storeId) {
                $data = array_merge($data, ['store_id' => $storeId, 'macro_id' => $macro->id]);
                logger()->channel('macro')->info('Run create alert: '.json_encode($data));

                $this->alertRepository()->createAlert($data);
            }
        }
    }

    private function getListStoreId(MacroConfiguration $macro): array
    {
        $listStoreId = $macro->listStoreId;
        /** @var \App\WebServices\OSS\ShopService */
        $shopService = app(ShopService::class);

        if (array_search(ShopConstant::SHOP_ALL_OPTION, $listStoreId) !== false) {
            $shopResult = $shopService->getList(['per_page' => -1]);

            if ($shopResult->get('success')) {
                $shops = $shopResult->get('data')->get('shops');

                return Arr::pluck($shops, 'store_id');
            }
        } elseif (array_search(ShopConstant::SHOP_OWNER_OPTION, $listStoreId) !== false) {
            $shopResult = $shopService->getList([
                'per_page' => -1,
                'own_manager' => $this->linkedUserInfoRepository()->getOssUserIdByCssUserId($macro->created_by),
            ]);

            if ($shopResult->get('success')) {
                $shops = $shopResult->get('data')->get('shops');

                $listStoreId = array_merge($listStoreId, Arr::pluck($shops, 'store_id'));
            }
        }

        return array_unique($listStoreId);
    }

    /**
     * Get data with increased start and end date time for the next new creation.
     */
    private function getDataWithDateTimeForNextCreation(
        array $data,
        string $startDateName,
        string $startTimeName,
        string $endDateName,
        string $endTimeName,
        int $diffInMinutes = 1,
    ) {
        $startDate = (new Carbon($data[$startDateName].' '.$data[$startTimeName]))
            ->addMinutes($diffInMinutes + 1);
        $endDate = (new Carbon($data[$endDateName].' '.$data[$endTimeName]))
            ->addMinutes($diffInMinutes + 1);
        $data[$startDateName] = $startDate->format('Y-m-d');
        $data[$startTimeName] = $startDate->format('H:i');

        $data[$endDateName] = $endDate->format('Y-m-d');
        $data[$endTimeName] = $endDate->format('H:i');

        return json_encode($data);
    }

    private function policyRepository(): PolicyRepository
    {
        return app(PolicyRepository::class);
    }

    private function taskRepository(): TaskRepository
    {
        return app(TaskRepository::class);
    }

    private function alertRepository(): AlertRepository
    {
        return app(AlertRepository::class);
    }

    private function linkedUserInfoRepository(): LinkedUserInfoRepository
    {
        return app(LinkedUserInfoRepository::class);
    }
}
