<?php

namespace App\Console\Commands;

use App\Project;
use Github\Client as GitHubClient;
use Github\ResultPager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncReposCommand extends Command
{
    protected $signature = 'sync:repos';

    protected $description = 'Sync projects with available GitHub repos';

    protected $client;

    public function __construct(GitHubClient $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    public function handle()
    {
        $projects = Project::all();
        $gitHubRepos = $this->fetchRepos();

        // Fetch all repos from the TightenCo GitHub account, and add any repos to the DB that do not
        // already exist. Ignore any repos that are forks of other packages for now. New repos are
        // active by default.
        $gitHubRepos->reject(function ($repo, $key) use ($projects) {
            $project = $projects->first(function ($project, $key) use ($repo) {
                return strtolower($project->name) == strtolower($repo['name']);
            });

            return ! is_null($project) || $repo['fork'];
        })->map(function ($repo) {
            list($vendor, $package) = explode("/", $repo['full_name']);

            Project::create([
                'name' => $repo['name'],
                'vendor' => $vendor,
                'package' => $package,
                'ignored' => false,
            ]);
        });

        $this->info("Finished Syncing {$gitHubRepos->count()} repos");
    }

    protected function fetchRepos()
    {
        return Cache::remember('repos', DAY_IN_SECONDS, function () {
            $githubClient = app(GitHubClient::class);

            $repos = (new ResultPager($githubClient))->fetchAll(
                $githubClient->api('organization'),
                'repositories',
                ['tightenco']
            );

            return collect($repos);
        });
    }
}
