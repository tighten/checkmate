<?php

namespace App\Console\Commands;

use App\Actions\ExtractRepositoryLaravelActionVersion;
use App\Actions\SyncProject;
use App\Exceptions\NotALaravelProject;
use App\Exceptions\QueryException;
use Illuminate\Console\Command;
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
        $this->info('Updating project data.');

        $this->repositories = collect();
        $this->fetchValidRepositories();

        $this->repositories->flatten(1)->each(function ($repository) {
            $this->processRepository($repository);
        });

        $this->info('Finished saving project data.');
    }

    private function fetchValidRepositories()
    {
        $this->info("Fetching repositories...\nGetting first page.");

        do {
            $response = $this->sendRequest();

            if (array_key_exists('errors', $response)) {
                throw new QueryException(collect($response['errors'])->pluck('message')->implode(PHP_EOL));
            }

            $this->addRepositoriesFromResponse($response);

            $nextPage = data_get($response, 'data.organization.repositories.pageInfo')['endCursor'];

            if ($nextPage) {
                $this->info('Getting another page.');
                $this->setNextPage($nextPage);
            }

        } while ($nextPage);
    }

    private function sendRequest(): array
    {
        $filters = $this->formatRepositoryFilters();

        $query = <<<QUERY
            {
              organization(login: "tighten") {
                id
                repositories({$filters}) {
                  totalCount
                  edges {
                    node {
                      name
                      owner {
                        login
                      }
                      isPrivate
                      composerJson: object(expression: "main:composer.json") {
                        id
                        ... on Blob {
                          text
                        }
                      }
                      composerLock: object(expression: "main:composer.lock") {
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
        $formattedRepositories = collect(data_get($response, 'data.organization.repositories.edges'))
            // Filter out repositories that do not have a composer.lock file
            ->filter(function ($repository) {
                return $repository['node']['composerLock'];
            })
            ->map(function ($repository) {
                try {
                    $laravelVersion = (new ExtractRepositoryLaravelActionVersion())($repository);
                } catch (NotALaravelProject $e) {
                    $laravelVersion = null;
                }

                return [
                    'vendor' => data_get($repository, 'node.owner.login'),
                    'name' => $repository['node']['name'],
                    'current_version' => $laravelVersion,
                    'constraint' => $this->extractConstraintFromJsonContents($repository),
                    'is_private' => $repository['node']['isPrivate'],
                ];
            })
            // Filter out repositories that do not have a laravel/framework dependency
            ->filter(function ($repository) {
                return $repository['current_version'];
            })
            ->values();

        $this->info('Storing ' . count($formattedRepositories) . ' repositories for processing.');
        $this->repositories->push($formattedRepositories);
    }

    private function processRepository($repository)
    {
        $this->info("Processing {$repository['vendor']}/{$repository['name']}...");

        if ($message = (new SyncProject())($repository)) {
            $this->info($message);
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

    private function extractConstraintFromJsonContents($repository)
    {
        return data_get(
            json_decode(data_get($repository, 'node.composerJson.text'), true), 'require.laravel/framework'
        );
    }
}
