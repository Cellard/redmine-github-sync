<?php


namespace App\IssueTracker\Contracts;


use Illuminate\Support\Collection;

/**
 * Server uses `priority` property to organize Issues
 * @package App\IssueTracker\Contracts
 */
interface WithPriority
{

    /**
     * Get project priority listing
     * @param ProjectContract $project
     * @return Collection|LabelContract[]
     */
    public function getPriorityListing(ProjectContract $project);
}
