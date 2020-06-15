<?php


namespace App\IssueTracker\Contracts;


use Illuminate\Support\Collection;

/**
 * Server uses Labels to organize Issues
 * @package App\IssueTracker\Contracts
 */
interface WithLabels
{
    /**
     * Get project labels
     * @param ProjectContract $project
     * @return Collection|LabelContract[]
     */
    public function getLabelListing(ProjectContract $project);
}
