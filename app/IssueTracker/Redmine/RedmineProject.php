<?php


namespace App\IssueTracker\Redmine;


use App\IssueTracker\Abstracts\Project;
use Illuminate\Support\Carbon;

class RedmineProject extends Project
{
    public static function createFromRemote(array $attributes, string $base_uri)
    {
        $attributes['slug'] = $attributes['identifier'];
        $attributes['url'] = $base_uri . '/projects/' . $attributes['slug'];
        return new static($attributes, $base_uri);
    }
    public static function createFromLocal(\App\Project $project)
    {
        $attributes = $project->toArray();
        $attributes['id'] = $project->ext_id;
        $attributes['url'] = $project->server->base_uri . '/projects/' . $attributes['slug'];
        return new static($attributes, $project->server->base_uri);
    }
}
