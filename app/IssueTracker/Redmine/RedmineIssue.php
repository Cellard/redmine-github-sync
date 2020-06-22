<?php


namespace App\IssueTracker\Redmine;


use App\IssueTracker\Abstracts\Issue;
use App\IssueTracker\Abstracts\Label;
use App\IssueTracker\Contracts\HasDates;
use App\IssueTracker\Contracts\HasPriority;
use App\IssueTracker\Contracts\HasStatus;
use App\IssueTracker\Contracts\HasTracker;
use App\IssueTracker\Contracts\ProjectContract;
use Carbon\Carbon;

class RedmineIssue extends Issue implements HasPriority, HasTracker, HasStatus, HasDates
{
    public static function createFromRemote(array $attributes, ProjectContract $project)
    {
        $attributes['started_at'] = $attributes['start_date'] ? Carbon::parse($attributes['start_date']) : null;
        $attributes['created_at'] = Carbon::parse($attributes['created_on']);
        $attributes['updated_at'] = Carbon::parse($attributes['updated_on']);
        $attributes['author'] = RedmineUser::createFromRemote($attributes['author']);
        $attributes['assignee'] = isset($attributes['fixed_version']) ? RedmineUser::createFromRemote($attributes['assigned_to']) : null;
        $attributes['tracker'] = Label::createFromRemote($attributes['tracker'], $project);
        $attributes['status'] = Label::createFromRemote($attributes['status'], $project);
        $attributes['priority'] = Label::createFromRemote($attributes['priority'], $project);
        $attributes['milestone'] = isset($attributes['fixed_version']) ? RedmineMilestone::createFromRemote($attributes['fixed_version'], $project) : null;
        $attributes['url'] = $project->base_uri . '/issues/' . $attributes['id'];

        return new static($attributes, $project);
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['tracker'] = $this->tracker->toArray();
        $data['status'] = $this->status->toArray();
        $data['priority'] = $this->priority->toArray();
        return $data;
    }
}
