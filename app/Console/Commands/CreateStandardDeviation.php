<?php

namespace App\Console\Commands;

use App\Jobs\CreateStandardDeviationJob;
use Illuminate\Console\Command;

class CreateStandardDeviation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-standard-deviation
        {--year-month= : YYYY-MM}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate the indexes to create the standard deviation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('year-month');

        CreateStandardDeviationJob::dispatch($date);
    }
}
