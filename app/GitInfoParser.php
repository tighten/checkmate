<?php

namespace App;

use Github\Client as GitHubClient;
use Illuminate\Cache\CacheManager;
use stdClass;

class GitInfoParser
{
    protected const CACHE_LENGTH = 5;
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
        return ltrim(collect($composerLock->packages)
            ->firstWhere('name', 'laravel/framework')
            ->version, 'v');
    }

    protected function composerJsonForRepo($vendor, $project)
    {
        return json_decode($this->fileForRepo($vendor, $project, 'composer.json'));
    }

    protected function composerLockForRepo($vendor, $project)
    {
        return json_decode($this->fileForRepo($vendor, $project, 'composer.lock'));
    }

    protected function fileForRepo($vendor, $project, $fileName)
    {
        $fileInfo = $this->github->api('repo')->contents()->show($vendor, $project, $fileName);

        return base64_decode($fileInfo['content']);
    }
}
