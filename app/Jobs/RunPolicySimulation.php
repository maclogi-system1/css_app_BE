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
        public Policy $simulation,
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

            $policySimulationHistoryRepository->create([
                'policy_id' => $this->simulation->id,
                'manager' => Arr::get($result, 'manager'),
                'title' =>  Arr::get($result, 'policy_name'),
                'job_title' => Arr::get($result, 'job_title'),
                'execution_time' => Arr::get($result, 'start_date'),
                'undo_time' => Arr::get($result, 'end_date'),
                'creation_date' => Arr::get($result, 'creation_date'),
                'sale_effect' => Arr::get($result, 'sale_effect'),
            ]);

            $this->simulation->processing_status = Policy::DONE_PROCESSING_STATUS;
            $this->simulation->save();

            DB::commit();
        } catch (\Throwable $e) {
            logger()->error('Run policy simulation: '.$e->getMessage());
            DB::rollBack();

            $this->simulation->processing_status = Policy::ERROR_PROCESSING_STATUS;
            $this->simulation->save();
        }
    }

    private function callApiRunPolicySimulation()
    {
        return [
            'creation_date' => now(),
            'policy_name' => $this->simulation->name,
            'manager' => 'User name',
            'start_date' => '2023-07-01 20:00:00',
            'end_date' => '2023-07-01 23:59:00',
            'sale_effect' => 20,
            'job_title' => 'Job title',
        ];
    }
}
