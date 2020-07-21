<?php

use App\LaravelVersion;
use App\Project;
use Faker\Generator as Faker;

$factory->define(Project::class, function (Faker $faker) {

    $version = factory(LaravelVersion::class)->create([
        'major' => '7',
        'minor' => '10',
        'patch' => '3',
    ]);

    return [
        'name' => $faker->sentence,
        'vendor' => 'tighten',
        'package' => $faker->slug,
        'current_laravel_version' => (string) $version,
        'current_laravel_constraint' => '^7.0',
        'is_valid' => true,
        'ignored' => false,
    ];
});

$factory->state(Project::class, 'ignored', [
    'ignored' => true,
]);
