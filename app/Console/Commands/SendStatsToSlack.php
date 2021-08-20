<?php

namespace App\Console\Commands;

use App\Notifications\SendCheckmateStats;
use App\OrgSlack;
use Illuminate\Console\Command;

class SendStatsToSlack extends Command
{
    protected $signature = 'stats:slack';

    protected $description = 'Send Checkmate stats to Slack';

    public function handle()
    {
        (new OrgSlack)->notify(new SendCheckmateStats);
    }
}
