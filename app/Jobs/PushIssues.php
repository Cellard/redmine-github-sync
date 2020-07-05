<?php

namespace App\Jobs;

use App\Factories\SynchronizerFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushIssues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mirror;

    public function __construct($mirror)
    {
        $this->mirror = $mirror;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $projects = $this->mirror->projects();
        foreach ($projects as $project) {
            $project['issuesToPush'] = $this->mirror->queryIssuesToPush($this->mirror->getProjectPosition($project))->get();
        }
        foreach ($projects as $project) {
            $synchronizer = (new SynchronizerFactory)->make($project->server);
            if (count($project['issuesToPush'])) {
                $synchronizer->pushIssues($project['issuesToPush'], $project, $this->mirror);
            }
        }
    }
}
