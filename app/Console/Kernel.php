<?php

namespace App\Console;

use App\Console\Commands\CreateMqSheetDefault;
use App\Console\Commands\ExecuteScheduledMacros;
use App\Jobs\CreateDefaultValueChains;
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
        $schedule->command(CreateDefaultValueChains::class)->dailyAt('05:00');
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
