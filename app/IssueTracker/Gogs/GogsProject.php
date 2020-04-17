<?php


namespace App\IssueTracker\Gogs;


use App\IssueTracker\Abstracts\Project;
use Illuminate\Support\Carbon;

class GogsProject extends Project
{
    public function getIdentifierAttribute()
    {
        return $this->full_name;
    }
    public function getCreatedAtAttribute($value)
    {
        return new Carbon($value);
    }
    public function getUrlAttribute()
    {
        return "{$this->base_uri}/{$this->identifier}";
    }
}
