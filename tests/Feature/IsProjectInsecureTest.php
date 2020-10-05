<?php

namespace Tests\Feature;

use App\LaravelVersion;
use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsProjectInsecureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_insecure_project_version_5()
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

        // then the project's should be insecure
        $this->assertEquals(true, $project->is_insecure);
    }

		function returns_secure_project_version_6()
    {
        // given there is a project at 5.3.30
				$project = factory(Project::class)->create([
            'current_laravel_version' => '6.14.0',
            'current_laravel_constraint' => '^6.0',
        ]);

        // and there is a laravel version for 5.3.35
				factory(LaravelVersion::class)->create([
            'major' => '6',
            'minor' => '18',
            'patch' => '0',
        ]);

        // then the project's should be insecure
        $this->assertEquals(false, $project->is_insecure);
    }
}
