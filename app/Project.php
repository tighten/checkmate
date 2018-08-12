<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id'];

    public function getLaravelConstraintAttribute()
    {
        return (new GitInfoParser)->laravelConstraint($this->vendor, $this->package);
    }

    public function getLaravelVersionAttribute()
    {
        return (new GitInfoParser)->laravelVersion($this->vendor, $this->package);
    }

    public function getDesiredLaravelVersionAttribute()
    {
        return app(LaravelVersions::class)->latestForVersion($this->laravel_version);
    }
}
