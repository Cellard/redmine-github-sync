<?php

namespace App\Jobs;

use App\Factories\SynchronizerFactory;
use App\Milestone;
use App\Mirror;
use App\Project;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullIssues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (Mirror::onlyClass([Project::class, Milestone::class])->get() as $mirror) {
            
            $syncedAt = Carbon::now()->subMinutes(5);

            foreach ($mirror->projects() as $project) {
                $synchronizer = (new SynchronizerFactory)->make($project->server, $mirror);
                $synchronizer->pullIssues($project, $mirror->synced_at->subDays(5) ?? $mirror->created_at->subDays(5));
            }

            $mirror->synced_at = $syncedAt;
            $mirror->save();
        }
    }
}
