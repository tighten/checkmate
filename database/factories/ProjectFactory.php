<?php

use App\Project;
use Faker\Generator as Faker;

$factory->define(Project::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'vendor' => 'tighten',
        'package' => $faker->slug,
        'current_laravel_version' => '5.3.31',
        'current_laravel_constraint' => '5.3.*',
        'ignored' => false,
    ];
});
