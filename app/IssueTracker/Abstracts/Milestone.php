<?php


namespace App\IssueTracker\Abstracts;

use App\IssueTracker\Contracts\MilestoneContract;
use App\IssueTracker\Contracts\ProjectContract;
use Jenssegers\Model\Model;

class Milestone extends Model implements MilestoneContract
{
    public function __construct(array $attributes, ProjectContract $project)
    {
        $attributes['project'] = $project;
        parent::__construct($attributes);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'due_on' => (string)$this->due_on,
            'project' => $this->project->toArray(),
            'url' => $this->url
        ];
    }
}
