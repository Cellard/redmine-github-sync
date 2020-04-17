<?php

namespace Tests\Feature;

use App\Facades\IssueTracker;
use App\Tracker;
use App\User;
use Tests\TestCase;

class TrackerTest extends TestCase
{

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testServiceProvider()
    {
        $user = factory(User::class)->create();
        $tracker = factory(Tracker::class)->create([
            'user_id' => $user->id
        ]);

        $service = IssueTracker::service('helpdesk.101m.ru', $tracker->api_key);

        $this->assertEquals($tracker->api_key, $service->getApiKey());
    }
}
