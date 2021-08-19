<?php

namespace App\Console\Commands;

use App\Actions\GetProjectsFromGitHub;
use App\Services\GitHub\Project;
use Illuminate\Console\Command;

class SyncProjects extends Command
{
    protected $signature = 'sync:projects';
    protected $description = 'Update all project versions from GitHub.';

    public function handle()
    {
        $this->info('Updating project data.');

        $this->fetchValidRepositories()->each(function ($repository) {
            $this->processRepository($repository);
        });

        $this->info('Finished saving project data.');
    }

    protected function fetchValidRepositories()
    {
        return (new GetProjectsFromGitHub($this))('tighten');
    }

    protected function processRepository($repository)
    {
        $this->info("Processing {$repository['vendor']}/{$repository['name']}...");

        if ($message = (new Project($repository))->sync()) {
            $this->info($message);
        }
    }
}
