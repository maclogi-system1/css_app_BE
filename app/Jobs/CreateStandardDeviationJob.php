<?php

namespace App\Jobs;

use App\Repositories\Contracts\StandardDeviationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateStandardDeviationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $yearMonth = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(StandardDeviationRepository $standardDeviationRepository): void
    {
        $date = $this->yearMonth ?? now()->subMonth()->format('Y-m');

        logger("Create standard deviation [{$date}]");

        $standardDeviationRepository->firstOrCreate([
            'date' => $date,
        ]);

        logger('Standard deviation has been created.');
    }
}
