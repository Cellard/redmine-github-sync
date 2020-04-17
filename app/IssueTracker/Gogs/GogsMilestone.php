<?php


namespace App\IssueTracker\Gogs;


use App\IssueTracker\Abstracts\Milestone;
use Illuminate\Support\Carbon;

class GogsMilestone extends Milestone
{
    public function getDueOnAttribute($value)
    {
        return $value ? new Carbon($value) : null;
    }
    public function getNameAttribute()
    {
        return $this->title;
    }
    public function getUrlAttribute()
    {
        return "{$this->project->url}/issues?state=open&milestone={$this->id}";
    }
}
