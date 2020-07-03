<?php

namespace App\Jobs;

use App\Factories\SynchronizerFactory;
use App\Milestone;
use App\Mirror;
use App\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushIssues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
         /** @var Mirror $mirror */
         foreach (Mirror::onlyClass([Project::class, Milestone::class])->get() as $mirror) {
            
            foreach ($mirror->projects() as $project) {
                $synchronizer = (new SynchronizerFactory)->make($project->server, $mirror);
                $issuesToPush = $mirror->queryIssuesToPush($mirror->getProjectPosition($project))->get();
                $synchronizer->pushIssues($issuesToPush, $project);
            }

        }
    }
}
