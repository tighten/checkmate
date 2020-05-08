<?php

use App\LaravelVersion;
use Faker\Generator as Faker;

$factory->define(LaravelVersion::class, function (Faker $faker) {
    return [
        'major' => (string) $faker->numberBetween(5, 6),
        'minor' => (string) $faker->numberBetween(0, 12),
        'patch' => (string) $faker->numberBetween(0, 40),
    ];
});
