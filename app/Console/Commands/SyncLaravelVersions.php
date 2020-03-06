<?php

namespace App\Console\Commands;

use App\LaravelVersion;
use Github\Client as GitHubClient;
use Github\ResultPager as GitHubResultPager;
use Illuminate\Console\Command;

class SyncLaravelVersions extends Command
{
    protected $signature = 'sync:laravel-versions';

    protected $description = 'Pull Laravel versions from GitHub into our application.';

    private $github;

    public function __construct(GitHubClient $github)
    {
        parent::__construct();

        $this->github = $github;
    }

    public function handle()
    {
        $versions = cache()->remember('github::laravel-versions', HOUR_IN_SECONDS, function () {
            $repositoryApi = $this->github->api('repo');

            $paginator = new GitHubResultPager($this->github);

            return $paginator->fetchAll($repositoryApi, 'tags', ['laravel', 'framework']);
        });

        collect($versions)
            // Map into arrays containing major, minor, and patch numbers
            ->map(function ($item) {
                $pieces = explode('.', ltrim($item['name'], 'v'));

                return [
                    'major' => $pieces[0],
                    'minor' => $pieces[1],
                    'patch' => $pieces[2] ?? null,
                ];
            })
            // Map into groups by major/minor pair such as 6.14, 6.13, 5.8, 5.7, etc
            ->mapToGroups(function ($item) {
                return [$item['major'] . '.' . $item['minor'] => $item];
            })
            // Take the highest patch number from each major/minor pair
            ->map(function ($item) {
                return $item->sortByDesc('patch')->first();
            })
            ->each(function ($item) {
                // Look for major/minor pair
                $version = LaravelVersion::where([
                    'major' => $item['major'],
                    'minor' => $item['minor'],
                ])->first();

                if (!$version) {
                    // Create it if it doesn't exist
                    return LaravelVersion::create([
                        'major' => $item['major'],
                        'minor' => $item['minor'],
                        'patch' => $item['patch'],
                    ]);
                }

                // Check if the current patch number is less
                // than what exists and update if so
                if ($version->patch < $item['patch']) {
                    $version->update(['patch' => $item['patch']]);
                }

                return $version;
            });
    }
}
