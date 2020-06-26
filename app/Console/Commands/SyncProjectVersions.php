<?php

namespace App\Console\Commands;

use App\Project;
use Github\Client as GitHubClient;
use Illuminate\Console\Command;

class SyncProjectVersions extends Command
{
    protected $signature = 'sync:project-versions {project?}';

    protected $description = 'Warm the cache with version info of all active projects that aren\'t already cached. Specify \'vendor/repo\' to hydrate a single project';

    protected $client;

    public function __construct(GitHubClient $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    public function handle()
    {
        $projects = $this->getProjects();

        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        $projects->map(function (Project $project) use ($bar) {
            $project->syncLaravelVersionAndConstraint();
            $bar->advance();
        });

        $bar->finish();
    }

    protected function getProjects()
    {
        if (is_null($this->argument('project'))) {
            return Project::active()->get();
        }

        list($vendor, $project) = explode("/", $this->argument('project'));
        $project = [Project::active()->where('project', $project)->first()] ?? [];

        return collect($project);
    }
}
