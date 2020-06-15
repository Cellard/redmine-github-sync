<?php


namespace App\IssueTracker\Abstracts;

use App\IssueTracker\Contracts\IssueContract;
use App\IssueTracker\Contracts\LabelContract;
use App\IssueTracker\Contracts\MilestoneContract;
use App\IssueTracker\Contracts\ProjectContract;
use App\IssueTracker\Contracts\UserContract;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Jenssegers\Model\Model;

/**
 * Class Issue
 * @package App\IssueTracker\Abstracts
 *
 */
class Issue extends Model implements IssueContract
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
            'open' => $this->open,
            'subject' => $this->subject,
            'description' => $this->description,
            'started_at' => (string)$this->started_at,
            'finished_at' => (string)$this->finished_at,
            'project' => $this->project->toArray(),
            'milestone' => optional($this->milestone)->toArray(),
            'author' => $this->author->toArray(),
            'assignee' => optional($this->assignee)->toArray(),
            'url' => $this->url
        ];
    }
}
