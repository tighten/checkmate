<?php

namespace App\Actions;

use App\Exceptions\NotALaravelProject;

class ExtractRepositoryLaravelConstraint
{
    protected $repository;

    public function __construct(array $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke()
    {
        $composerLockContents = json_decode(data_get($this->repository, 'node.composerLock.text'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // May actually be a Laravel project, but it's broken somehow
            throw new NotALaravelProject('Error decoding composer.lock for ' . $this->repository['node']['name']);
        }

        $laravelFrameworkEntry = collect($composerLockContents['packages'])->firstWhere('name', 'laravel/framework');

        if (! $laravelFrameworkEntry) {
            throw new NotALaravelProject('laravel/framework not found in lock file for ' . $this->repository['node']['name']);
        }

        return ltrim($laravelFrameworkEntry['version'], 'v');
    }
}
