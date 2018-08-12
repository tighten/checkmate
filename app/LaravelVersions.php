<?php

namespace App;

use Exception;
use Github\Client as GitHubClient;
use Github\ResultPager as GitHubResultPager;
use Illuminate\Cache\CacheManager;

class LaravelVersions
{
    private $github;
    private $cache;

    public function __construct(GitHubClient $github, CacheManager $cache)
    {
        $this->github = $github;
        $this->cache = $cache;
    }

    public function latest()
    {
        return $this->trim(
            $this->github->api('repo')->releases()->latest('laravel', 'framework')['tag_name']
        );
    }

    /**
     * Return the latest version number in 5.6.23 format
     * given a "minor" (5.6)
     *
     * @todo: If it's been a super long time, we're gonna have to paginate;
     * either add pagination here, or cache them, or re-work this architecture
     *
     * @param  string $minor 5.6 or similar
     * @return string        5.6.23 or similar
     */
    public function latestForMinor($minor)
    {
        if ($this->cache->has($this->cacheKeyForMinor($minor))) {
            return $this->cache->get($this->cacheKeyForMinor($minor));
        }

        $releasesApi = $this->github->api('repo')->releases();
        $paginator = new GitHubResultPager($this->github);

        $result = $paginator->fetch($releasesApi, 'all', ['laravel', 'framework']);

        // @todo: Figure out whether smarter people would have a better solution
        while (true) {
            foreach ($result as $release) {
                if ($this->minorFromRelease($release['tag_name']) == $minor) {
                    $this->cache->put($this->cacheKeyForMinor($minor), $this->trim($release['tag_name']), 60);
                    return $this->trim($release['tag_name']);
                }
            }

            if (! $paginator->hasNext()) {
                break;
            }

            $result = $paginator->fetchNext();
        }

        // @todo: Why is it not matching <5.3? Is there a page limit?
        throw new Exception("Nothing matches minor version [{$minor}]; sorry!");
    }

    /**
     * Gives the latest release number for the minor version represented by the passed verison
     *
     * @param  string $version 5.6.23 or similar
     * @return string          5.6.33 or similar
     */
    public function latestForVersion($version)
    {
        return $this->latestForMinor($this->minorFromRelease($version));
    }

    public function trim($version)
    {
        return ltrim($version, 'v');
    }

    public function minorFromRelease($version)
    {
        $version = $this->trim($version); // Just in case

        return substr(
            $version,
            0,
            strrpos($version, '.')
        );
    }

    private function cacheKeyForMinor($minor)
    {
        return 'laravel-version-latest-for-minor::' . $minor;
    }
}
