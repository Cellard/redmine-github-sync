<?php

namespace Tests\Feature;

use App\Facades\IssueTracker;
use App\IssueTracker\Contracts\IssueTrackerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GogsTest extends IssueTrackerTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = IssueTracker::service('git.101m.ru', '78ccba0ae923524ba2301c3cccfc25d7a26e09cb');
    }

}
