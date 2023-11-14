<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\StandardDeviationRepository;
use Illuminate\Console\Command;

class CreateStandardDeviation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-standard-deviation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var \App\Repositories\Contracts\StandardDeviationRepository */
        $standardDeviationRepository = app(StandardDeviationRepository::class);
        $date = now()->subMonth();

        $standardDeviationRepository->firstOrCreate([
            'date' => $date->format('Y-m'),
        ]);
    }
}
