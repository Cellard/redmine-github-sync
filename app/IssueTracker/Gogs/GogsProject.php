<?php


namespace App\IssueTracker\Gogs;


use App\IssueTracker\Abstracts\Project;
use Illuminate\Support\Carbon;

class GogsProject extends Project
{
    public static function createFromRemote(array $attributes, string $base_uri)
    {
        $attributes['slug'] = $attributes['full_name'];
        $attributes['url'] = $base_uri . '/' . $attributes['slug'];
        return new static($attributes, $base_uri);
    }
    public static function createFromLocal(\App\Project $project)
    {
        $attributes = $project->toArray();
        $attributes['id'] = $project->ext_id;
        $attributes['url'] = $project->server->base_uri . '/' . $attributes['slug'];
        return new static($attributes, $project->server->base_uri);
    }
}
