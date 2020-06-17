<?php


namespace App\IssueTracker\Gogs;


use App\IssueTracker\Abstracts\Issue;
use App\IssueTracker\Abstracts\Label;
use App\IssueTracker\Contracts\HasLabels;
use App\IssueTracker\Contracts\LabelContract;
use App\IssueTracker\Contracts\MilestoneContract;
use App\IssueTracker\Contracts\ProjectContract;
use App\IssueTracker\Contracts\UserContract;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GogsIssue extends Issue implements HasLabels
{
    public static function createFromRemote(array $attributes, ProjectContract $project)
    {
        $attributes['id'] = $attributes['number'];
        $attributes['subject'] = $attributes['title'];
        $attributes['description'] = $attributes['body'];
        $attributes['open'] = $attributes['state'] == 'open' ? true : false;

        if ($attributes['milestone']) {
            $attributes['milestone'] = GogsMilestone::createFromRemote($attributes['milestone'], $project);
        }

        $attributes['author'] = GogsUser::createFromRemote($attributes['user']);

        if ($attributes['assignee']) {
            $attributes['assignee'] = GogsUser::createFromRemote($attributes['assignee']);
        }

        $attributes['labels'] = collect((array)@$attributes['labels'])->map(function ($label) use ($project) {
            return Label::createFromRemote($label, $project);
        });

        $attributes['url'] = $project->url . '/issues/' . $attributes['id'];

        return new static($attributes, $project);
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['labels'] = $this->labels->toArray();
        return $data;
    }
}
