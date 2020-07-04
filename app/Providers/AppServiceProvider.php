<?php

namespace App\Providers;

use App\Issue;
use App\Observers\IssueObserver;
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
        Issue::observe(IssueObserver::class);
    }
}
