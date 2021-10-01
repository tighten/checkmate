<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LaravelVersion extends Model
{
    // @see support policy https://laravel.com/docs/8.x/releases#support-policy
    public const SECURITY_FIX_END_DATES = [
        6 => 'September 6th, 2022',
        7 => 'March 3rd, 2021',
        8 => 'January 24th, 2023',
        9 => 'January 28th, 2025',
        10 => 'January 28th, 2025',
    ];

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
