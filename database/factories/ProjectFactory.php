<?php

use App\LaravelVersion;
use App\Project;
use Faker\Generator as Faker;

$factory->define(Project::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'vendor' => 'tighten',
        'package' => $faker->slug,
        'current_laravel_version' => function () {
            return factory(LaravelVersion::class)->create()->__toString();
        },
        'current_laravel_constraint' => '^7.0',
        'is_valid' => true,
        'ignored' => false,
        'is_private' => rand(0, 1),
    ];
});

$factory->state(Project::class, 'ignored', [
    'ignored' => true,
]);
