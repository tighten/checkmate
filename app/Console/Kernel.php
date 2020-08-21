<?php

namespace App\Console;

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
        $schedule->command(SyncLaravelVersions::class)->twiceDaily(1, 13);
        $schedule->command(SyncProjects::class)->twiceDaily(2, 14);
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
