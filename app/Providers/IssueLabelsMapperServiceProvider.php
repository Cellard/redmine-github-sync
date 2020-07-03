<?php

namespace App\Providers;

use App\Services\IssueLabelsMapper;
use Illuminate\Support\ServiceProvider;

class IssueLabelsMapperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('IssueLabelsMapper', function () {
            return new IssueLabelsMapper;
        });
    }
}
