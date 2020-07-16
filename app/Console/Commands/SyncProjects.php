<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

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
        $this->info("Updating Tighten's projects");

        do {
            $response = $this->sendRequest();

            if (array_key_exists('errors', $response)) {
                // @todo: proper error handling
                dd('problem...', $response);
            }

            $this->addRepositoriesFromResponse($response);

            $nextPage = data_get($response, 'data.organization.repositories.pageInfo')['endCursor'];

            if ($nextPage) {
                $this->setNextPage($nextPage);
            }

        } while ($nextPage);

        $this->info('All data has been fetched.');

        $this->line('Starting to repositories...');

        $this->repositories->flatten(1)->each(function ($repository) {
            $this->line("Processing {$repository['vendor']}/{$repository['name']}...");

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
                $this->info("Updating {$project->name}'s version data...");

                $project->update([
                    'current_laravel_version' => $repository['current_version'],
                    'current_laravel_constraint' => $repository['constraint'],
                    'is_valid' => true,
                ]);
            }
        });

        $this->info('Projects are updated.');
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

        $this->line("Sending request for {$this->defaultFilters['first']} repositories...");

        return Http::withToken(config('services.github.token'))
            ->post('https://api.github.com/graphql', ['query' => $query])
            ->json();
    }

    private function addRepositoriesFromResponse(array $response)
    {
        $this->line('Parsing response...');

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
        );
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
        $this->info('More data to fetch...');

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
}
