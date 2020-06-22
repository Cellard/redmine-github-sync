<?php

namespace App\Providers;

use App\Issue;
use App\Observers\IssueObserver;
use App\Observers\SyncedIssueObserver;
use App\Observers\UserObserver;
use App\SyncedIssue;
use App\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Issue::observe(IssueObserver::class);
    }
}
