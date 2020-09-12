<?php

namespace Tests\Feature;

use App\Exceptions\QueryException;
use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncingProjectsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function fetches_tighten_projects_and_their_version_information()
    {
        Http::fake([
            'api.github.com/graphql' => Http::response(file_get_contents('tests/responses/projects-response.json')),
        ]);

        $this->artisan('sync:projects');

        $this->assertEquals(2, Project::count(), 'Example response contained 2 valid projects');

        tap(Project::where('name', 'checkmate')->first(), function ($checkmate) {
            $this->assertEquals('7.20.0', $checkmate->current_laravel_version);
            $this->assertEquals('^7.0', $checkmate->current_laravel_constraint);
            $this->assertTrue($checkmate->is_valid);
            $this->assertFalse($checkmate->ignored);
        });

        tap(Project::where('name', 'ozzie')->first(), function ($ozzie) {
            $this->assertEquals('7.8.1', $ozzie->current_laravel_version);
            $this->assertEquals('^7.0', $ozzie->current_laravel_constraint);
            $this->assertTrue($ozzie->is_valid);
            $this->assertFalse($ozzie->ignored);
        });
    }

    /** @test */
    function throws_query_exception_when_query_has_errors()
    {
        $this->expectException(QueryException::class);

        Http::fake(['api.github.com/graphql' => Http::response(file_get_contents('tests/responses/errors.json'))]);

        $this->artisan('sync:projects');
    }

    /** @test */
    function updates_project_if_version_has_changed()
    {
        $project = factory(Project::class)->create([
            'vendor' => 'tighten',
            'name' => 'checkmate',
            'package' => 'checkmate',
            'current_laravel_version' => '7.19.0',
        ]);

        Http::fake([
            'api.github.com/graphql' => Http::response(file_get_contents('tests/responses/projects-response.json')),
        ]);

        $this->artisan('sync:projects');

        $this->assertEquals('7.20.0', $project->fresh()->current_laravel_version);
    }

    /** @test */
    function update_project_if_constaint_has_changed()
    {
        $project = factory(Project::class)->create([
            'vendor' => 'tighten',
            'name' => 'checkmate',
            'package' => 'checkmate',
            'current_laravel_constraint' => '^6.0',
        ]);

        Http::fake([
            'api.github.com/graphql' => Http::response(file_get_contents('tests/responses/projects-response.json')),
        ]);

        $this->artisan('sync:projects');

        $this->assertEquals('^7.0', $project->fresh()->current_laravel_constraint);
    }
}
