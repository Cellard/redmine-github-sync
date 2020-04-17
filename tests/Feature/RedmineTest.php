<?php

namespace Tests\Feature;

use App\Facades\IssueTracker;
use App\IssueTracker\Contracts\IssueTrackerInterface;
use App\IssueTracker\Redmine\RedmineProject;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RedmineTest extends IssueTrackerTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = IssueTracker::service('helpdesk.101m.ru', '8bb7831f1fbff76d2ec0e0eb8f2304fd4a96068c');
    }
}
