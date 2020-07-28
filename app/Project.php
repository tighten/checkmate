<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
