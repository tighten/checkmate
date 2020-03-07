<?php

namespace App\Console\Commands;

use App\Exceptions\NotALaravelProjectException;
use App\Project;
use Github\Client as GitHubClient;
use Exception;
use Illuminate\Console\Command;

class SyncPackageVersions extends Command
{
    protected $signature = 'sync:packageversions {package?}';

    protected $description = 'Warm the cache with version info of all active projects that aren\'t already cached. Specify \'vendor/package\' to hydrate a single package';

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

        $projects->map(function (Project $project, $key) use ($bar) {
            // For now we don't care what the exception was that was thrown by the GitInfoParser or
            // the LaravelVersions parser. We swallow the exception and continue.
            try {
                // $this->syncProjectVersion($project);

                $project->syncLaravelVersionAndConstraint();

            } catch (NotALaravelProjectException $e) {
                $project->update(['ignored' => true]);
            }

            $bar->advance();
        });

        $bar->finish();
    }

    protected function syncProjectVersion($project)
    {
        $project->laravel_constraint;
        $project->laravel_version;
        $project->desired_laravel_version;
    }

    protected function getProjects()
    {
        if (is_null($this->argument('package'))) {
            return Project::active()->get();
        }

        list($vendor, $package) = explode("/", $this->argument('package'));
        $project = [Project::active()->where('package', $package)->first()] ?? [];

        return collect($project);
    }
}
