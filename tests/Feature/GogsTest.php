<?php

namespace Tests\Feature;

use App\IssueTracker\Gogs\GogsIssueTracker;
use App\Project;
use App\Server;

class GogsTest extends IssueTrackerTest
{
    /**
     * @var GogsIssueTracker
     */
    protected $service;
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Server::find('git.101m.ru')->connect('78ccba0ae923524ba2301c3cccfc25d7a26e09cb');
    }

    public function testLabels()
    {
        /** @var Project $project */
        $project = Project::query()
            ->where('server_id', 'git.101m.ru')
            ->where('slug', 'Cellard/redmine-sync')
            ->firstOrFail();

        $labels = $this->service->getLabelListing($project->contract());

        dd($labels);

    }

}
