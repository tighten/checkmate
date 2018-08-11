<?php

use App\Project;
use Faker\Generator as Faker;

$factory->define(Project::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'vendor' => $faker->slug,
        'package' => $faker->slug,
    ];
});
