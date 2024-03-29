<?php

namespace App\Console;

use App\Console\Commands\SendStatsToSlack;
use App\Console\Commands\SyncLaravelVersions;
use App\Console\Commands\SyncProjects;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command(SyncLaravelVersions::class)->everyTenMinutes();
        $schedule->command(SyncProjects::class)->everyTenMinutes();

        $schedule->command(SendStatsToSlack::class)
            ->weekly()
            ->fridays()
            ->at('06:00');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
