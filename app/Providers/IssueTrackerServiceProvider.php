<?php

namespace App\Providers;

use App\IssueTracker\IssueTrackerManager;
use App\Redmine;
use Illuminate\Support\ServiceProvider;
use function foo\func;

class IssueTrackerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('issueTracker', function ($app) {
            return new IssueTrackerManager($app);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
