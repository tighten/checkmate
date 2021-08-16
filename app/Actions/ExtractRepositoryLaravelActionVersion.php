<?php

namespace App\Actions;

use App\Exceptions\NotALaravelProject;

class ExtractRepositoryLaravelActionVersion
{
    public function __invoke(array $repository)
    {
        $composerLockContents = json_decode(data_get($repository, 'node.composerLock.text'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // May actually be a Laravel project, but it's broken somehow
            throw new NotALaravelProject('Error decoding composer.lock for ' . $repository['node']['name']);
        }

        $laravelFrameworkEntry = collect($composerLockContents['packages'])->firstWhere('name', 'laravel/framework');

        if (! $laravelFrameworkEntry) {
            throw new NotALaravelProject('laravel/framework not found in lock file for ' . $repository['node']['name']);
        }

        return ltrim($laravelFrameworkEntry['version'], 'v');
    }
}
