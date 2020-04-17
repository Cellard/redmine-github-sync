<?php


namespace App\IssueTracker\Redmine;


use App\IssueTracker\Abstracts\Milestone;
use Illuminate\Support\Carbon;

class RedmineMilestone extends Milestone
{
    public function getDueOnAttribute()
    {
        return $this->due_date ? new Carbon($this->due_date) : null;
    }

    public function getUrlAttribute()
    {
        return "{$this->project->base_uri}/versions/{$this->id}";
    }
}
