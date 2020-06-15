<?php


namespace App\IssueTracker\Contracts;


use Illuminate\Support\Collection;

/**
 * Server uses `tracker` (type of issue) property to organize Issues
 * @package App\IssueTracker\Contracts
 */
interface WithTracker
{
    /**
     * Get project tracker listing
     * @param ProjectContract $project
     * @return Collection|LabelContract[]
     */
    public function getTrackerListing(ProjectContract $project);
}
