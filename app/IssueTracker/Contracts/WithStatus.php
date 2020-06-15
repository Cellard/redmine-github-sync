<?php


namespace App\IssueTracker\Contracts;


use Illuminate\Support\Collection;

/**
 * Server uses `status` (progress of issue) property to organize Issues
 * @package App\IssueTracker\Contracts
 */
interface WithStatus
{
    /**
     * Get project status listing
     * @param ProjectContract $project
     * @return Collection|LabelContract[]
     */
    public function getStatusListing(ProjectContract $project);
}
