<?php


namespace App\IssueTracker\Contracts;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use Jenssegers\Model\Model;

/**
 * Interface IssueContract
 * @package App\IssueTracker\Contracts
 *
 * @mixin Model
 * @property-read integer $id
 * @property-read string $subject
 * @property-read null|string $description
 * @property-read null|boolean $open
 * @property-read ProjectContract $project
 * @property-read null|MilestoneContract $milestone
 * @property-read UserContract $author
 * @property-read null|UserContract $assignee
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read string $url
 */
interface IssueContract
{
    public function toArray();
}
