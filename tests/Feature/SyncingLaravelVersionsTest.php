<?php

namespace Tests\Feature;

use App\LaravelVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SyncingLaravelVersionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_import_laravel_versions()
    {
        // Response has 4 versions, including 7.19.0 and 7.19.1.
        // We expect 7.19.0 to be ignored since 7.19.1 in present.
        Http::fake(['api.github.com/graphql' => Http::response(file_get_contents('tests/responses/versions.json'))]);

        $this->artisan('sync:laravel-versions');

        $this->assertEquals(3, LaravelVersion::count());

        collect([
            ['major' => '7', 'minor' => '20', 'patch' => '0'],
            ['major' => '7', 'minor' => '19', 'patch' => '1'],
            ['major' => '6', 'minor' => '18', 'patch' => '25'],
        ])->each(function ($set) {
            $this->assertTrue(LaravelVersion::where($set)->exists());
        });
    }

    /** @test */
    function updates_versions_to_latest_patch_release()
    {
        $laravelVersion = factory(LaravelVersion::class)->create([
            'major' => '7',
            'minor' => '19',
            'patch' => '0',
        ]);

        // Response includes version 7.19.1
        Http::fake(['api.github.com/graphql' => Http::response(file_get_contents('tests/responses/versions.json'))]);

        $this->artisan('sync:laravel-versions');

        tap($laravelVersion->fresh(), function ($version) {
            // Patch version should have updated
            $this->assertEquals('1', $version->patch);

            // Ensure the other values are unchanged
            $this->assertEquals('7', $version->major);
            $this->assertEquals('19', $version->minor);
        });
    }

    /** @test */
    function it_can_build_an_error_message_from_response_json()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("Error connecting to GitHub: Something went wrong 1. Something went wrong 2");

        Http::fake(['api.github.com/graphql' => Http::response(file_get_contents('tests/responses/multiple-errors.json'), 500)]);

        $this->artisan('sync:laravel-versions');
    }

    /** @test */
    function it_provides_a_generic_error_message_when_response_json_has_no_error_key()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("Error connecting to GitHub: No error message provided.");

        Http::fake(['api.github.com/graphql' => Http::response(file_get_contents('tests/responses/versions.json'), 500)]);

        $this->artisan('sync:laravel-versions');
    }
}
