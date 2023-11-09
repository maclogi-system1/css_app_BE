<?php

namespace App\Jobs;

use App\Models\Policy;
use App\Models\User;
use App\Repositories\Contracts\PolicySimulationHistoryRepository;
use App\WebServices\AI\StorePred2mService;
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
                $policyId = Arr::get($item, 'policy_id');
                $title = Arr::get($item, 'name');
                $storePred2m = Arr::get($item, 'store_pred_2m');
                $itemsPred2m = Arr::get($item, 'items_pred_2m');
                $policies = Arr::get($item, 'policies', []);

                if (count($policies)) {
                    foreach ($policies as $suggestPolicy) {
                        $policySimulationHistoryRepository->create([
                            'policy_id' => $policyId,
                            'title' => $title,
                            'execution_time' => Arr::get($suggestPolicy, 'start_date'),
                            'undo_time' => Arr::get($suggestPolicy, 'end_date'),
                            'creation_date' => now(),
                            'sale_effect' => 0,
                            'store_pred_2m' => $storePred2m,
                            'items_pred_2m' => $itemsPred2m,
                            'user_id' => $this->manager?->id,
                            'class' => Arr::get($suggestPolicy, 'class'),
                            'service' => Arr::get($suggestPolicy, 'service'),
                            'value' => Arr::get($suggestPolicy, 'value'),
                            'condition_1' => Arr::get($suggestPolicy, 'condition_1'),
                            'condition_value_1' => Arr::get($suggestPolicy, 'condition_value_1'),
                            'condition_2' => Arr::get($suggestPolicy, 'condition_2'),
                            'condition_value_2' => Arr::get($suggestPolicy, 'condition_value_2'),
                            'condition_3' => Arr::get($suggestPolicy, 'condition_3'),
                            'condition_value_3' => Arr::get($suggestPolicy, 'condition_value_3'),
                        ]);
                    }
                } else {
                    $policySimulationHistoryRepository->create([
                        'policy_id' => $policyId,
                        'title' => $title,
                        'execution_time' => Arr::get($item, 'start_date'),
                        'undo_time' => Arr::get($item, 'end_date'),
                        'creation_date' => now(),
                        'sale_effect' => 0,
                        'store_pred_2m' => $storePred2m,
                        'items_pred_2m' => $itemsPred2m,
                        'user_id' => $this->manager?->id,
                    ]);
                }

                $simulation = Policy::find(Arr::get($item, 'policy_id'));
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

            $result[] = $this->callApiRunPolicySimulation([
                'store_id' => $this->storeId,
                'policies' => array_map(function ($rule) use ($startDate, $endDate) {
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
                }, $simulation['rules']),
            ]) + [
                'policy_id' => $simulation['id'],
                'name' => $simulation['name'],
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
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
}
