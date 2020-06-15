<?php


namespace App\IssueTracker\Contracts;


use App\Milestone;
use App\Project;
use Illuminate\Support\Carbon;
use Jenssegers\Model\Model;

/**
 * Interface MilestoneContract
 * @package App\IssueTracker\Contracts
 *
 * @mixin Model
 * @property-read integer $id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read Carbon|null $due_on
 * @property-read ProjectContract $project
 * @property-read string $url
 */
interface MilestoneContract
{
    public function toArray();

    /**
     * Создает локальную копию записи
     * @param Project $project
     * @return Milestone
     */
    public function toLocal(Project $project);
}
