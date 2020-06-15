<?php


namespace App\IssueTracker\Contracts;


use App\IssueTracker\AccessException;
use Illuminate\Support\Collection;

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
     * Get current user
     * @return UserContract
     * @throws AccessException
     */
    public function getAccount();

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
    public function getMilestones(ProjectContract $project);

    /**
     * Get project issue listing
     * @param ProjectContract $project
     * @return Collection|IssueContract[]
     */
    public function getIssues(ProjectContract $project);
}
