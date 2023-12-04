<?php

namespace App\Console;

use App\Console\Commands\CreateDefaultValueChainsForShops;
use App\Console\Commands\CreateMqSheetDefault;
use App\Console\Commands\CreateStandardDeviation;
use App\Console\Commands\ExecuteScheduledMacros;
use App\Console\Commands\GetInferenceStorePred36m;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(ExecuteScheduledMacros::class)->everyMinute();
        $schedule->command(CreateMqSheetDefault::class)->daily();
        $schedule->command(CreateDefaultValueChainsForShops::class)->monthlyOn(3, '5:0');
        $schedule->command(CreateStandardDeviation::class)->monthlyOn(1, '5:0');
        $schedule->command(GetInferenceStorePred36m::class)->monthlyOn(1);
        $schedule->command(GetInferenceStorePred36m::class, ['--generate-data'])->monthlyOn(1, '2:0');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
