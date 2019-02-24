<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id'];

    public function getLaravelConstraintAttribute()
    {
        return app(GitInfoParser::class)->laravelConstraint($this->vendor, $this->package);
    }

    public function getLaravelVersionAttribute()
    {
        return app(GitInfoParser::class)->laravelVersion($this->vendor, $this->package);
    }

    public function getDesiredLaravelVersionAttribute()
    {
        return app(LaravelVersions::class)->latestForVersion($this->laravel_version);
    }

    public function getIsBehindLatestAttribute()
    {
        return version_compare($this->desired_laravel_version, $this->laravel_version) > 0;
    }

    public function presentStatus()
    {
        if ($this->is_behind_latest) {
            return '<span style="font-weight: bold; color: red;">BEHIND</span>';
        }

        return '<span style="font-weight: bold; color: green;">CURRENT</span>';
    }
}
