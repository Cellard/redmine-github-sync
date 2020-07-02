<?php

namespace App\Jobs;

use App\Factories\ConnectorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullIssues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $project;
    private $mirror;
    private $connector;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($project, $mirror)
    {
        $this->project = $project;
        $this->mirror = $mirror;
        $this->connector = (new ConnectorFactory)->make($this->project->server);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $apiKey = $this->mirror->user->credentials()->where('server_id', $this->project->server_id)->first()->api_key;
        $this->connector->connect($this->project->server->base_uri, $apiKey);
        $this->connector->pullIssues($this->project, $this->mirror->synced_at ?? $this->mirror->created_at);
    }
}
