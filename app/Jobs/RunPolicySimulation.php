<?php

namespace App\Jobs;

use App\Models\Policy;
use App\Models\User;
use App\Repositories\Contracts\PolicySimulationHistoryRepository;
use App\WebServices\AI\StorePred2mService;
use App\WebServices\AI\SuggestPolicyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RunPolicySimulation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $storeId,
        public array $data = [],
        #[WithoutRelations]
        public User $manager,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        PolicySimulationHistoryRepository $policySimulationHistoryRepository,
    ): void {
        try {
            DB::beginTransaction();

            $result = $this->handleSimulations();

            foreach ($result as $item) {
                $policyId = Arr::get($item, 'pred_2m.policy_id');
                $title = Arr::get($item, 'pred_2m.name');
                $storePred2m = Arr::get($item, 'pred_2m.store_pred_2m');
                $itemsPred2m = Arr::get($item, 'pred_2m.items_pred_2m');
                $policyPredId = Arr::get($item, 'suggest_policy.pred_id');

                $policySimulationHistoryRepository->create([
                    'policy_id' => $policyId,
                    'title' => $title,
                    'execution_time' => Arr::get($item, 'pred_2m.start_date'),
                    'undo_time' => Arr::get($item, 'pred_2m.end_date'),
                    'creation_date' => now(),
                    'sale_effect' => 0,
                    'store_pred_2m' => $storePred2m,
                    'items_pred_2m' => $itemsPred2m,
                    'policy_pred_id' => $policyPredId,
                    'user_id' => $this->manager?->id,
                ]);

                $simulation = Policy::find($policyId);
                $simulation->processing_status = Policy::DONE_PROCESSING_STATUS;
                $simulation->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            logger()->error('Run policy simulation: '.$e->getMessage());
            DB::rollBack();

            Policy::whereIn('id', Arr::pluck($this->data, 'id'))->update([
                'processing_status' => Policy::ERROR_PROCESSING_STATUS,
            ]);
        }
    }

    private function handleSimulations()
    {
        $result = [];
        $timezone = config('app.timezone');

        foreach ($this->data as $simulation) {
            $startDate = Carbon::create($simulation['simulation_start_date'])->setTimezone($timezone);
            $endDate = Carbon::create($simulation['simulation_end_date'])->setTimezone($timezone);

            $rules = array_map(function ($rule) use ($startDate, $endDate) {
                return [
                    'class' => $rule['class'],
                    'service' => $rule['service'],
                    'value' => $rule['value'],
                    'start_date' => $startDate->format('Y-m-d H:i'),
                    'end_date' => $endDate->format('Y-m-d H:i'),
                    'condition_1' => $rule['condition_1'],
                    'condition_value_1' => $rule['condition_value_1'],
                    'condition_2' => $rule['condition_2'],
                    'condition_value_2' => $rule['condition_value_2'],
                    'condition_3' => $rule['condition_3'],
                    'condition_value_3' => $rule['condition_value_3'],
                ];
            }, $simulation['rules']);

            $pred2m = $this->callApiRunPolicySimulation([
                'store_id' => $this->storeId,
                'policies' => $rules,
            ]) + [
                'policy_id' => $simulation['id'],
                'name' => $simulation['name'],
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
            ];

            $suggestPolicy = $this->callApiSuggestPolicy($this->storeId, [
                'name' => $simulation['name'],
                'simulation_start_date' => $startDate->format('Y-m-d'),
                'simulation_start_time' => $startDate->format('H:i'),
                'simulation_end_date' => $endDate->format('Y-m-d'),
                'simulation_end_time' => $endDate->format('H:i'),
                'simulation_promotional_expenses' => $simulation['simulation_promotional_expenses'],
                'simulation_store_priority' => intval($simulation['simulation_store_priority']),
                'simulation_product_priority' => intval($simulation['simulation_product_priority']),
                'policy_rules' => [],
            ]);

            $result[] = [
                'pred_2m' => $pred2m,
                'suggest_policy' => $suggestPolicy,
            ];
        }

        return $result;
    }

    private function callApiRunPolicySimulation(array $dataRequest)
    {
        /** @var \App\WebServices\AI\StorePred2mService */
        $storePred2mService = app(StorePred2mService::class);
        $result = $storePred2mService->runSimulation($dataRequest);

        if (! $result->get('success')) {
            logger()->error($result->get('data')->toJson());

            throw new RuntimeException('Calling the api to run the policy simulation from the AI side failed.');
        }

        return $result->get('data')->get('data');
    }

    private function callApiSuggestPolicy(string $storeId, array $dataRequest): array
    {
        /** @var \App\WebServices\AI\SuggestPolicyService */
        $suggestPolicyService = app(SuggestPolicyService::class);
        $result = $suggestPolicyService->runSuggestPolicyForSimulation($storeId, $dataRequest);

        if (! $result->get('success')) {
            logger()->error($result->get('data')->toJson());

            throw new RuntimeException('Calling the API to get the suggested policy from the AI side failed.');
        }

        return $result->get('data')->get('body');
    }
}
