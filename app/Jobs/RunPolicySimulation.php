<?php

namespace App\Jobs;

use App\Models\Policy;
use App\Repositories\Contracts\PolicySimulationHistoryRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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

            $result = $this->callApiRunPolicySimulation();

            if (is_null($result)) {
                throw new RuntimeException('Calling the api to run the policy simulation from the AI side failed.');
            }

            foreach (Arr::get($result, 'data', []) as $item) {
                $policySimulationHistoryRepository->create([
                    'policy_id' => Arr::get($item, 'id'),
                    'title' => Arr::get($item, 'name'),
                    'execution_time' => Arr::get($item, 'simulation_start_date'),
                    'undo_time' => Arr::get($item, 'simulation_end_date'),
                    'creation_date' => now(),
                    'sale_effect' => Arr::get($item, 'sale_effect', 0),
                ]);
                $simulation = Policy::find(Arr::get($item, 'id'));
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

    private function callApiRunPolicySimulation()
    {
        $dataRequest = [
            'store_id' => $this->storeId,
            'data' => array_map(function ($simulation) {
                return [
                    'id' => $simulation['id'],
                    'name' => $simulation['name'],
                    'simulation_start_date' => Carbon::parse($simulation['simulation_start_date'])->format('Y-m-d H:i:s'),
                    'simulation_end_date' => Carbon::parse($simulation['simulation_end_date'])->format('Y-m-d H:i:s'),
                    'simulation_promotional_expenses' => $simulation['simulation_promotional_expenses'],
                    'policy_rules' => array_map(function ($rule) {
                        return [
                            'id' => $rule['id'],
                            'class' => $rule['class'],
                            'service' => $rule['service'],
                            'value' => $rule['value'],
                        ];
                    }, $simulation['rules']),
                    'created_at' => Carbon::parse($simulation['created_at'])->format('Y-m-d H:i:s'),
                ];
            }, $this->data),
        ];

        return $dataRequest;
    }
}
