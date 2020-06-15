<?php


namespace App\IssueTracker\Abstracts;

use App\IssueTracker\Contracts\MilestoneContract;
use App\IssueTracker\Contracts\ProjectContract;
use App\Project;
use Jenssegers\Model\Model;

/**
 * Class Milestone
 * @package App\IssueTracker\Abstracts
 *
 * @property-read ProjectContract $project
 */
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

    /**
     * @param Project $project
     * @return \App\Milestone
     */
    public function toLocal(Project $project)
    {
        /** @var \App\Milestone $milestone */
        $milestone = \App\Milestone::withTrashed()->updateOrCreate(
            [
                'project_id' => $project->id,
                'ext_id' => $this->id
            ],
            [
                'name' => $this->name,
                'description' => $this->description,
                'due_on' => $this->due_on
            ]
        );
        $milestone->restore();
        return $milestone;
    }
}
