<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SyncProjectVersions extends Command
{
    protected $signature = 'sync:project-versions';

    protected $description = 'Update all project versions from GitHub.';

    protected $repositories;

    private $defaultFilters = [
        'first' => '100',
        'isFork' => 'false',
        'orderBy' => '{field: CREATED_AT, direction: ASC}',
    ];

    public function handle()
    {
        do {
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

            $response = Http::withToken(config('services.github.token'))
                ->post('https://api.github.com/graphql', ['query' => $query])
                ->json();

            if (array_key_exists('errors', $response)) {
                // @todo: proper error handling
                dd('problem...', $response);
            }

            $this->addRepositoriesFromResponse($response);

            $nextPage = $this->getNextPage($response);

            if ($nextPage) {
                $this->setNextPage($response);
            }

        } while ($nextPage);

        $this->repositories->flatten(1)->each(function ($repository) {
            $project = Project::firstOrCreate([
                'name' => $repository['name'],
                'vendor' => $repository['vendor'],
                'package' => $repository['name'],
            ], [
                'current_laravel_version' => $repository['current_version'],
                'current_laravel_constraint' => $repository['constraint'],
                'is_valid' => true,
            ]);

            if ($project->current_laravel_version !== $repository['current_version'] || $project->current_laravel_constraint !== $repository['constraint']) {
                $project->update([
                    'current_laravel_version' => $repository['current_version'],
                    'current_laravel_constraint' => $repository['constraint'],
                    'is_valid' => true,
                ]);
            }
        });

        $this->info('Done...');
    }

    private function addRepositoriesFromResponse(array $response)
    {
        if (! $this->repositories instanceof Collection) {
            $this->repositories = collect();
        }

        $this->repositories->push(
            collect(data_get($response, 'data.organization.repositories.edges'))
                ->filter(function ($repository) {
                    return $repository['node']['composerLock'];
                })
                ->map(function ($repository) {

                    $vendor = data_get($repository, 'node.owner.login');

                    $composerJson = json_decode(data_get($repository, 'node.composerJson.text'), true);

                    $constraint = data_get($composerJson, 'require.laravel/framework');

                    $laravelFramework = collect(
                        json_decode(
                            data_get($repository, 'node.composerLock.text'),
                            true
                        )['packages'])
                        ->firstWhere('name', 'laravel/framework');

                    $laravelVersion = ltrim($laravelFramework['version'] ?? null, 'v');

                    return [
                        'vendor' => $vendor,
                        'name' => $repository['node']['name'],
                        'current_version' => $laravelVersion,
                        'constraint' => $constraint,
                    ];
                })
                ->filter(function ($repository) {
                    return $repository['current_version'];
                })
                ->values()
        )->flatten(1);
    }

    private function formatRepositoryFilters()
    {
        return collect($this->defaultFilters)
            ->map(function ($value, $key) {
                return "{$key}: $value";
            })
            ->implode(', ');
    }

    private function getNextPage(array $response)
    {
        return data_get($response, 'data.organization.repositories.pageInfo')['endCursor'];
    }

    private function setNextPage(array $response)
    {
        $this->defaultFilters['after'] = '"' . data_get($response,
                'data.organization.repositories.pageInfo')['endCursor'] . '"';
    }
}
