<?php


namespace Tests\Feature;


use App\IssueTracker\Contracts\IssueTrackerInterface;
use Illuminate\Support\Collection;
use Tests\TestCase;

abstract class IssueTrackerTest extends TestCase
{
    /**
     * @var IssueTrackerInterface
     */
    protected $service;

    public function testProjects()
    {
        $projects = $this->service->getProjects();

        $this->assertTrue($projects instanceof Collection);

        dump($projects->first()->toArray());

        foreach ($projects as $project) {
            $milestones = $this->service->getMilestones($project);

            if ($milestones->count()) {
                dump($milestones->first()->toArray());
                break;
            }

            //dump($project->name);
        }
    }


}
