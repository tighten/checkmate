<?php

namespace Tests\Feature;

use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewProjectsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function not_ignored_projects_show_on_home_page()
    {
        factory(Project::class)->create(['name' => 'my-awesome-project']);
        factory(Project::class)->state('ignored')->create(['name' => 'my-ignored-project']);

        $this->get(route('projects.index'))
            ->assertSeeText('my-awesome-project')
            ->assertDontSeeText('my-ignored-project');
    }

    /** @test */
    function only_ignored_projects_show_on_ignored_page()
    {
        factory(Project::class)->state('ignored')->create(['name' => 'my-ignored-project']);
        factory(Project::class)->create(['name' => 'my-awesome-project']);

        $this->get(route('ignored.index'))
            ->assertSeeText('my-ignored-project')
            ->assertDontSeeText('my-awesome-project');
    }

    /** @test */
    function private_projects_dont_show_on_home_page()
    {
        config()->set('app.show_private_repos', false);

        factory(Project::class)->state('private')->create(['name' => 'my-private-project']);
        factory(Project::class)->create(['name' => 'my-awesome-project']);

        $this->get(route('projects.index'))
            ->assertDontSeeText('my-private-project');
    }

    /** @test */
    function private_projects_dont_show_on_ignored_page()
    {
        config()->set('app.show_private_repos', false);

        factory(Project::class)->states('private', 'ignored')->create(['name' => 'my-ignored-private-project']);

        $this->get(route('ignored.index'))
            ->assertDontSeeText('my-ignored-private-project');
    }
}
