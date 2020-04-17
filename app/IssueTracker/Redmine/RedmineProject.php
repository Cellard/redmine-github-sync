<?php


namespace App\IssueTracker\Redmine;


use App\IssueTracker\Abstracts\Project;
use Illuminate\Support\Carbon;

class RedmineProject extends Project
{
    public function getCreatedAtAttribute()
    {
        return new Carbon($this->created_on);
    }
    public function getUrlAttribute()
    {
        return "{$this->base_uri}/projects/{$this->identifier}";
    }
}
