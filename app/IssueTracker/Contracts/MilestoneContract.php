<?php


namespace App\IssueTracker\Contracts;


use Illuminate\Support\Carbon;
use Jenssegers\Model\Model;

/**
 * Interface MilestoneContract
 * @package App\IssueTracker\Contracts
 *
 * @mixin Model
 * @property-read integer $id
 * @property-read string $name
 * @property-read string $description
 * @property-read Carbon $due_on
 * @property-read ProjectContract $project
 * @property-read string $url
 */
interface MilestoneContract
{

}
