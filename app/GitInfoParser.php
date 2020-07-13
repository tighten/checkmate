<?php

namespace App;

use App\Exceptions\ComposerJsonFileNotFound;
use App\Exceptions\ComposerLockFileNotFound;
use App\Exceptions\NotALaravelProject;
use Github\Client as GitHubClient;
use Github\Exception\RuntimeException;
use Illuminate\Cache\CacheManager;
use stdClass;

class GitInfoParser
{
    protected const CACHE_LENGTH = HOUR_IN_SECONDS;

    private $github;
    private $cache;

    public function __construct(GitHubClient $github, CacheManager $cache)
    {
        $this->github = $github;
        $this->cache = $cache;
    }

    public function laravelConstraint($vendor, $package)
    {
        return $this->cache->remember("{$vendor}/{$package}--laravel-constraint", static::CACHE_LENGTH, function () use ($vendor, $package) {
            return $this->laravelConstraintFromComposerJson(
                $this->composerJsonForRepo($vendor, $package)
            );
        });
    }

    public function laravelVersion($vendor, $package)
    {
        return $this->cache->remember("{$vendor}/{$package}--laravel-version", static::CACHE_LENGTH, function () use ($vendor, $package) {
            return $this->laravelVersionFromComposerLock(
                $this->composerLockForRepo($vendor, $package)
            );
        });
    }

    protected function laravelConstraintFromComposerJson(stdClass $composerJson)
    {
        return $composerJson->require->{"laravel/framework"};
    }

    protected function laravelVersionFromComposerLock(stdclass $composerLock)
    {
        $laravelDetails = collect($composerLock->packages)->firstWhere('name', 'laravel/framework');

        if (! $laravelDetails) {
            throw new NotALaravelProject('laravel/framework does not exist in composer lock file');
        }

        return ltrim($laravelDetails->version, 'v');
    }

    protected function composerJsonForRepo($vendor, $project)
    {
        try {
            return json_decode($this->fileForRepo($vendor, $project, 'composer.json'));
        } catch (RuntimeException $e) {
            throw new ComposerJsonFileNotFound("composer.json file does not exist for {$project}");
        }
    }

    protected function composerLockForRepo($vendor, $project)
    {
        try {
            return json_decode($this->fileForRepo($vendor, $project, 'composer.lock'));
        } catch (RuntimeException $e) {
            throw new ComposerLockFileNotFound("composer.lock file does not exist for {$project}");
        }
    }

    protected function fileForRepo($vendor, $project, $fileName)
    {
        $fileInfo = $this->github->api('repo')->contents()->show($vendor, $project, $fileName);

        return base64_decode($fileInfo['content']);
    }
}
