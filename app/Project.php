<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Project extends Model
{
    public const STATUS_BEHIND = 'behind';
    public const STATUS_CURRENT = 'current';
    public const STATUS_INSECURE = 'insecure';

    protected $guarded = ['id'];

    protected $casts = [
        'ignored' => 'boolean',
        'is_valid' => 'boolean',
    ];

    public const DESIRED_VERSION_CACHE_KEY = 'project-desired-version--%s';

    public function getDesiredLaravelVersionAttribute()
    {
        return cache()->remember(sprintf(self::DESIRED_VERSION_CACHE_KEY, $this->id), HOUR_IN_SECONDS, function () {
            [$major, $minor] = explode('.', $this->current_laravel_version);

            $query = LaravelVersion::query()->where('major', $major);
            $sortColumn = 'minor';

            // If checking against the legacy version scheme then we're focusing
            // on the highest patch version within the set minor version
            if ((int) $major <= 5) {
                $query = $query->where('minor', $minor);
                $sortColumn = 'patch';
            }

            return (string) $query->get()
                ->tap(function ($collection) {
                    if ($collection->count() === 0) {
                        throw (new ModelNotFoundException)->setModel(Project::class);
                    }
                })
                ->sortByDesc(function ($version) use ($sortColumn) {
                    return (int) $version->$sortColumn;
                })
                ->first();
        });
    }

    public function getGithubUrlAttribute()
    {
        return 'https://github.com/' . $this->vendor . '/' . $this->package;
    }

    public function getIsBehindLatestAttribute()
    {
        return version_compare($this->desired_laravel_version, $this->current_laravel_version) > 0;
    }

    public function getStatusAttribute()
    {
        if (!$this->is_behind_latest) {
            return self::STATUS_CURRENT;
        }

        return $this->hasSecurityFixes() ? self::STATUS_BEHIND : self::STATUS_INSECURE;
    }

    private function hasSecurityFixes()
    {
        $major= explode('.', $this->current_laravel_version)[0];

        if ($major === '8') {
            // @see https://laravel.com/docs/8.x/releases#support-policy
            return strtotime('September 8th, 2021') >= strtotime('today');
        }
        if ($major === '7') {
            // @see https://laravel.com/docs/7.x/releases#support-policy
            return strtotime('March 3rd, 2021') >= strtotime('today');
        }
        if ($major === '6') {
            // @see https://laravel.com/docs/6.x/releases#support-policy
            return strtotime('September 3rd, 2022') >= strtotime('today');
        }

        return false;
    }

    public function scopeActive($query)
    {
        return $query->where('ignored', 0);
    }

    public function scopeIgnored($query)
    {
        return $query->where('ignored', 1);
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    public function isBehind()
    {
        return $this->status === self::STATUS_BEHIND;
    }

    public function isCurrent()
    {
        return $this->status === self::STATUS_CURRENT;
    }

    public function isInsecure()
    {
        return $this->status === self::STATUS_INSECURE;
    }
}
