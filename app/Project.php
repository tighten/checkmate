<?php

namespace App;

use App\GitInfoParser;
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
}
