<?php

namespace Tests\Feature;

use App\Server;

class RedmineTest extends IssueTrackerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Server::find('helpdesk.101m.ru')->connect('8bb7831f1fbff76d2ec0e0eb8f2304fd4a96068c');
    }
}
