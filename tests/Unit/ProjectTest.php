<?php

namespace Tests\Unit;

use App\LaravelVersion;
use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function project_with_desired_version_has_current_status()
    {
        factory(LaravelVersion::class)->create([
            'major' => '6',
            'minor' => '3',
            'patch' => '0',
        ]);
        $project = factory(Project::class)->make([
            'current_laravel_version' => '6.3.0',
        ]);

        $status = $project->status;

        $this->assertSame(Project::STATUS_CURRENT, $status);
        $this->assertTrue($project->isCurrent());
    }

    /** @test */
    public function project_with_lower_version_has_behind_status()
    {
        factory(LaravelVersion::class)->create([
            'major' => '6',
            'minor' => '2',
            'patch' => '1',
        ]);
        $project = factory(Project::class)->make([
            'current_laravel_version' => '6.1.1',
        ]);

        $status = $project->status;

        $this->assertSame(Project::STATUS_BEHIND, $status);
        $this->assertTrue($project->isBehind());
    }

    /** @test */
    public function project_with_lower_version_and_unsuported_laravel_has_insecure_status()
    {
        factory(LaravelVersion::class)->create([
            'major' => '5',
            'minor' => '1',
            'patch' => '20',
        ]);
        $project = factory(Project::class)->make([
            'current_laravel_version' => '5.1.11',
        ]);

        $status = $project->status;

        $this->assertSame(Project::STATUS_INSECURE, $status);
        $this->assertTrue($project->isInsecure());
    }

    /** @test */
    public function project_with_desired_version_has_insecure_status_because_unsupported_laravel()
    {
        factory(LaravelVersion::class)->create([
            'major' => '5',
            'minor' => '1',
            'patch' => '20',
        ]);
        $project = factory(Project::class)->make([
            'current_laravel_version' => '5.1.20',
        ]);

        $status = $project->status;

        $this->assertSame(Project::STATUS_INSECURE, $status);
        $this->assertTrue($project->isInsecure());
    }
}
