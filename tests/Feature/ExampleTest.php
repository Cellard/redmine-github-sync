<?php

namespace Tests\Feature;

use App\Mirror;
use App\Services\Synchronizers\LocalRedmineSynchronizer;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase, InteractsWithExceptionHandling;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLocalRedmineSynchronizerPullIssues()
    {
        $mirror = factory(Mirror::class)->create();
        $redmineClientMock = \Mockery::mock('overload:\Redmine\Client');
        $redmineClientIssueMock = \Mockery::mock('overload:\Redmine\Api\Issue');
        $redmineClientIssueMock->shouldReceive('all')
            ->andReturn([]);
        $synchronizer = new LocalRedmineSynchronizer($mirror->left->server);
        $synchronizer->pullIssues($mirror->left, $mirror, null, null);
        $this->assertTrue(true);
    }
}
