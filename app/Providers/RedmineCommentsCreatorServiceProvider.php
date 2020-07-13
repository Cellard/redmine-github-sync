<?php

namespace App\Providers;

use App\Services\RedmineCommentsCreator;
use Illuminate\Support\ServiceProvider;

class RedmineCommentsCreatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('RedmineCommentsCreator', function () {
            return new RedmineCommentsCreator;
        });
    }
}
