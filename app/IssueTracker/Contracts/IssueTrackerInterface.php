<?php


namespace App\IssueTracker\Contracts;


use Illuminate\Support\Collection;
use Jenssegers\Model\Model;

/**
 * Interface IssueTrackerInterface
 * @package App\IssueTracker\Contracts
 *
 */
interface IssueTrackerInterface
{
    public function getBaseUri();

    public function getApiKey();

    /**
     * Get project listing
     * @param int $page
     * @return Collection|ProjectContract[]
     */
    public function getProjects($page = 1);

    /**
     * Get project milestone listing
     * @param ProjectContract $project
     * @return Collection|MilestoneContract[]
     */
    public function getMilestones($project);
}
