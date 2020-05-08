<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LaravelVersion extends Model
{
    protected $guarded = ['id'];

    public function __toString()
    {
        return implode('.', [
            $this->major,
            $this->minor,
            $this->patch,
        ]);
    }
}
