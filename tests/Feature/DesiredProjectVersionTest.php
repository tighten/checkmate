<?php

namespace Tests\Feature;

use App\LaravelVersion;
use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesiredProjectVersionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_desired_version_for_legacy_versioning_scheme()
    {
        // given there is a project at 5.3.30
        $project = factory(Project::class)->create([
            'current_laravel_version' => '5.3.30',
            'current_laravel_constraint' => '5.3.*',
        ]);

        // and there is a laravel version for 5.3.35
        factory(LaravelVersion::class)->create([
            'major' => '5',
            'minor' => '3',
            'patch' => '35',
        ]);

        // and the latest laravel version is 5.4.35
        factory(LaravelVersion::class)->create([
            'major' => '5',
            'minor' => '4',
            'patch' => '35',
        ]);

        // then the project's desired version should stick to the 5.3 branch
        $this->assertEquals('5.3.35', $project->desired_laravel_version);
    }

    /** @test */
    function returns_desired_version_for_modern_versioning_scheme()
    {
        // given there is a project at 6.14
        $project = factory(Project::class)->create([
            'current_laravel_version' => '6.14.0',
            'current_laravel_constraint' => '^6.0',
        ]);

        // and the latest laravel version is 6.18.0
        factory(LaravelVersion::class)->create([
            'major' => '6',
            'minor' => '18',
            'patch' => '0',
        ]);

        // then the project's desired version should be 6.18.0
        $this->assertEquals('6.18.0', $project->desired_laravel_version);
    }

    /** @test */
    function returns_the_latest_version_for_the_legacy_versioning_scheme()
    {
        // given a version 5.3.10 exists
        factory(LaravelVersion::class)->create([
            'major' => '5',
            'minor' => '3',
            'patch' => '10',
        ]);

        // and a version 5.3.12 exists
        factory(LaravelVersion::class)->create([
            'major' => '5',
            'minor' => '3',
            'patch' => '12',
        ]);

        // when a laravel 5.3 project exists
        $project = factory(Project::class)->create([
            'current_laravel_version' => '5.3.1',
            'current_laravel_constraint' => '^5.3.*',
        ]);

        // then the desired version should be 5.3.12
        $this->assertEquals('5.3.12', $project->desired_laravel_version);
    }

    /** @test */
    function returns_the_latest_version_for_the_modern_versioning_scheme()
    {
        // given a version 7.18.0 exists
        factory(LaravelVersion::class)->create([
            'major' => '7',
            'minor' => '18',
            'patch' => '0',
        ]);

        // and a version 7.19.0 exists
        factory(LaravelVersion::class)->create([
            'major' => '7',
            'minor' => '19',
            'patch' => '0',
        ]);

        // when a laravel 7 project exists
        $project = factory(Project::class)->create([
            'current_laravel_version' => '7.10.0',
            'current_laravel_constraint' => '^7.0',
        ]);

        // then the desired version should be 7.19.0
        $this->assertEquals('7.19.0', $project->desired_laravel_version);
    }
}
