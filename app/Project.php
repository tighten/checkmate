<?php

namespace App;

use App\Exceptions\ComposerJsonFileNotFound;
use App\Exceptions\ComposerLockFileNotFound;
use App\Exceptions\NotALaravelProject;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'ignored' => 'boolean',
        'is_valid' => 'boolean',
    ];

    public function getDesiredLaravelVersionAttribute()
    {
        [$major, $minor] = explode('.', $this->current_laravel_version);

        // @todo: cache this
        $version = LaravelVersion::where([
            'major' => $major,
            'minor' => $minor,
        ])->firstOrFail();

        return (string) $version;
    }

    public function getGithubUrlAttribute()
    {
        return 'https://github.com/' . $this->vendor . '/' . $this->package;
    }

    public function getIsBehindLatestAttribute()
    {
        return version_compare($this->desired_laravel_version, $this->current_laravel_version) > 0;
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
}
