<?php

namespace App\Actions;

use App\Exceptions\NotALaravelProject;
use App\Exceptions\QueryException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class GetProjectsFromGitHub
{
    protected $command;
    protected $defaultFilters = [
        'first' => '100',
        'isFork' => 'false',
        'orderBy' => '{field: CREATED_AT, direction: ASC}',
    ];
    protected $repositories;
    protected $orgLogin;

    public function __construct(Command $command)
    {
        $this->command = $command;
        $this->repositories = collect();
    }

    public function __invoke(string $orgLogin): Collection
    {
        $this->orgLogin = $orgLogin;

        $this->info('Fetching repositories...');
        $this->info('Getting first page.');

        do {
            $response = $this->sendRequest();

            $this->addRepositoriesFromResponse($response);
            $nextPage = $this->handleNextPage($response);
        } while ($nextPage);

        return $this->repositories->flatten(1);
    }

    protected function sendRequest(): array
    {
        $response = Http::withToken(config('services.github.token'))
            ->post('https://api.github.com/graphql', ['query' => $this->buildQuery()])
            ->json();

        if (array_key_exists('errors', $response)) {
            throw new QueryException(collect($response['errors'])->pluck('message')->implode(PHP_EOL));
        }

        return $response;
    }

    protected function buildQuery(): string
    {
        return <<<QUERY
            {
              organization(login: "{$this->orgLogin}") {
                id
                repositories({$this->formatRepositoryFilters()}) {
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
    }

    protected function formatRepositoryFilters(): string
    {
        return collect($this->defaultFilters)
            ->map(function ($value, $key) {
                return "{$key}: $value";
            })
            ->implode(', ');
    }

    protected function addRepositoriesFromResponse(array $response): void
    {
        $formattedRepositories = collect(data_get($response, 'data.organization.repositories.edges'))
            // Filter out repositories that do not have a composer.lock file
            ->filter(function ($repository) {
                return $repository['node']['composerLock'];
            })
            ->map(function ($repository) {
                try {
                    $laravelVersion = (new ExtractRepositoryLaravelConstraint($repository))();
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

        $this->info(sprintf('Storing %s repositories for processing.', count($formattedRepositories)));
        $this->repositories->push($formattedRepositories);
    }

    protected function handleNextPage($response): ?string
    {
        $nextPage = data_get($response, 'data.organization.repositories.pageInfo')['endCursor'] ?? null;

        if ($nextPage) {
            $this->info('Getting another page.');
            $this->setNextPage($nextPage);
        }

        return $nextPage ?: null;
    }

    protected function setNextPage($page)
    {
        $this->defaultFilters['after'] = sprintf('"%s"', $page);
    }

    protected function extractConstraintFromJsonContents(array $repository): ?string
    {
        return data_get(
            json_decode(data_get($repository, 'node.composerJson.text'), true),
            'require.laravel/framework'
        );
    }

    protected function info(string $message): void
    {
        $this->command->info($message);
    }
}
