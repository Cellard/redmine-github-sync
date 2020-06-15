<?php


namespace App\IssueTracker\Gogs;


use App\IssueTracker\Abstracts\Milestone;
use App\IssueTracker\Contracts\ProjectContract;
use Carbon\Carbon;

class GogsMilestone extends Milestone
{
    public static function createFromRemote(array $attributes, ProjectContract $project)
    {
        $attributes['due_on'] = $attributes['due_on'] ? Carbon::parse($attributes['due_on']) : null;
        $attributes['name'] = $attributes['title'];
        $attributes['url'] = $project->url . '/issues?state=open&milestone=' . $attributes['id'];
        return new static($attributes, $project);
    }
}
