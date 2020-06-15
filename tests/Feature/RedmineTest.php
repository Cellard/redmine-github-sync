<?php

namespace Tests\Feature;

use App\IssueTracker\Redmine\RedmineIssueTracker;
use App\Project;
use App\Server;

class RedmineTest extends IssueTrackerTest
{
    /**
     * @var RedmineIssueTracker
     */
    protected $service;
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Server::find('helpdesk.101m.ru')->connect('8bb7831f1fbff76d2ec0e0eb8f2304fd4a96068c');
    }

    public function testLabels()
    {
        /** @var Project $project */
        $project = Project::query()
            ->where('server_id', 'helpdesk.101m.ru')
            ->firstOrFail();

        $labels = $this->service->getStatusListing($project->contract());

        dd($labels->toArray());

    }
}
