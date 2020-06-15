<?php


namespace App\IssueTracker\Redmine;


use App\IssueTracker\Abstracts\Milestone;
use App\IssueTracker\Contracts\ProjectContract;
use Carbon\Carbon;

class RedmineMilestone extends Milestone
{
    public static function createFromRemote(array $attributes, ProjectContract $project)
    {
        $attributes['due_on'] = @$attributes['due_date'] ? Carbon::parse($attributes['due_date']) : null;
        $attributes['url'] = $project->url . '/versions/' . $attributes['id'];
        return new static($attributes, $project);
    }
}
