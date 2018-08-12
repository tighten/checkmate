<?php

namespace App\Providers;

use App\LaravelVersions;
use Github\Client as GitHubClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LaravelVersions::class, LaravelVersions::class);

        $this->app->bind(GitHubClient::class, function ($app) {
            $client = new GitHubClient;
            $client->authenticate(config('services.github.token'), null, GitHubClient::AUTH_HTTP_TOKEN);
            return $client;
        });
    }
}
