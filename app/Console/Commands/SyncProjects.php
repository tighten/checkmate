<?php

namespace App\Console\Commands;

use App\Exceptions\NotALaravelProject;
use App\Exceptions\QueryException;
use App\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncProjects extends Command
{
    protected $signature = 'sync:projects';

    protected $description = 'Update all project versions from GitHub.';

    protected $repositories;

    private $defaultFilters = [
        'first' => '100',
        'isFork' => 'false',
        'orderBy' => '{field: CREATED_AT, direction: ASC}',
    ];

    public function handle()
    {
        Log::info("Updating project versions");

        $this->fetchValidRepositories();

        $this->repositories->flatten(1)->each(function ($repository) {
            $this->processRepository($repository);
        });

        Log::info('Projects versions are updated');
    }

    private function fetchValidRepositories()
    {
        do {
            $response = $this->sendRequest();

            if (array_key_exists('errors', $response)) {
                throw new QueryException(collect($response['errors'])->pluck('message')->implode(PHP_EOL));
            }

            $this->addRepositoriesFromResponse($response);

            $nextPage = data_get($response, 'data.organization.repositories.pageInfo')['endCursor'];

            if ($nextPage) {
                $this->setNextPage($nextPage);
            }

        } while ($nextPage);
    }

    private function sendRequest()
    {
        $filters = $this->formatRepositoryFilters();

        $query = <<<QUERY
            {
              organization(login: "tightenco") {
                id
                repositories($filters) {
                  totalCount
                  edges {
                    node {
                      name
                      owner {
                        login
                      }
                      composerJson: object(expression: "master:composer.json") {
                        id
                        ... on Blob {
                          text
                        }
                      }
                      composerLock: object(expression: "master:composer.lock") {
                        id
                        ... on Blob {
                          text
                        }
                      }
                    }
                  }
                  pageInfo {
                    endCursor
                    hasNextPage
                  }
                }
              }
              rateLimit {
                cost
                limit
                remaining
                resetAt
              }
            }
        QUERY;

        return Http::withToken(config('services.github.token'))
            ->post('https://api.github.com/graphql', ['query' => $query])
            ->json();
    }

    private function addRepositoriesFromResponse(array $response)
    {
        if (! $this->repositories instanceof Collection) {
            $this->repositories = collect();
        }

        $formattedRepositories = collect(data_get($response, 'data.organization.repositories.edges'))
            // filter out repositories that do not have a composer.lock file
            ->filter(function ($repository) {
                return $repository['node']['composerLock'];
            })
            ->map(function ($repository) {
                try {
                    $laravelVersion = $this->extractLaravelVersionFromLockContents($repository);
                } catch (NotALaravelProject $e) {
                    $laravelVersion = null;
                }

                return [
                    'vendor' => data_get($repository, 'node.owner.login'),
                    'name' => $repository['node']['name'],
                    'current_version' => $laravelVersion,
                    'constraint' => $this->extractConstraintFromJsonContents($repository),
                ];
            })
            // filter out repositories that do not have a laravel/framework dependency
            ->filter(function ($repository) {
                return $repository['current_version'];
            })
            ->values();

        $this->repositories->push($formattedRepositories);
    }

    private function processRepository($repository)
    {
        Log::info("Processing {$repository['vendor']}/{$repository['name']}...");

        $project = Project::firstOrCreate([
            'name' => $repository['name'],
            'vendor' => $repository['vendor'],
            'package' => $repository['name'],
        ], [
            'current_laravel_version' => $repository['current_version'],
            'current_laravel_constraint' => $repository['constraint'],
            'is_valid' => true,
        ]);

        if ($this->versionDataHasChanged($project, $repository)) {
            Log::info("Updating {$project->name}'s version...");

            $project->update([
                'current_laravel_version' => $repository['current_version'],
                'current_laravel_constraint' => $repository['constraint'],
                'is_valid' => true,
            ]);

            cache()->forget(sprintf(Project::DESIRED_VERSION_CACHE_KEY, $project->id));
        }
    }

    private function formatRepositoryFilters()
    {
        return collect($this->defaultFilters)
            ->map(function ($value, $key) {
                return "{$key}: $value";
            })
            ->implode(', ');
    }

    private function setNextPage($page)
    {
        $this->defaultFilters['after'] = '"' . $page . '"';
    }

    private function versionDataHasChanged($project, $repository)
    {
        return $this->versionHasChanged($project, $repository['current_version'])
            || $this->constraintHasChanged($project, $repository['constraint']);
    }

    private function versionHasChanged($project, $currentVersion)
    {
        return $project->current_laravel_version !== $currentVersion;
    }

    private function constraintHasChanged($project, $constaint)
    {
        return $project->current_laravel_constraint !== $constaint;
    }

    private function extractLaravelVersionFromLockContents($repository)
    {
        $composerLockContents = json_decode(data_get($repository, 'node.composerLock.text'), true);

        $laravelFrameworkEntry = collect($composerLockContents['packages'])->firstWhere('name', 'laravel/framework');

        if (! $laravelFrameworkEntry) {
            throw new NotALaravelProject('laravel/framework not found in lock file');
        }

        return ltrim($laravelFrameworkEntry['version'], 'v');
    }

    private function extractConstraintFromJsonContents($repository)
    {
        return data_get(
            json_decode(data_get($repository, 'node.composerJson.text'), true), 'require.laravel/framework'
        );
    }
}
