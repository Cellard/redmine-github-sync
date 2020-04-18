<?php

namespace Tests\Feature;

use App\Server;

class GogsTest extends IssueTrackerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Server::find('git.101m.ru')->connect('78ccba0ae923524ba2301c3cccfc25d7a26e09cb');
    }

}
