<?php

namespace Tests\Feature;

use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnIgnoreProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_unignore_project()
    {
        $project = factory(Project::class)->state('ignored')->create();

        $this->patch(route('project.unignore', $project));

        $this->assertFalse($project->fresh()->ignored);
    }
}
