<?php

namespace Tests\Feature;

use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IgnoreProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_ignore_a_project()
    {
        $project = factory(Project::class)->create(['ignored' => false]);

        $this->patch(route('project.ignore', $project));

        $this->assertTrue($project->fresh()->ignored);
    }
}
