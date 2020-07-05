<?php

namespace App\Jobs;

use App\Factories\SynchronizerFactory;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullIssues implements ShouldQueue
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
        $syncedAt = Carbon::now()->subMinutes(5);

        foreach ($this->mirror->projects() as $project) {
            $synchronizer = (new SynchronizerFactory)->make($project->server);
            $synchronizer->pullIssues($project, $this->mirror, $this->mirror->synced_at, $this->mirror->start_date);
        }

        $this->mirror->synced_at = $syncedAt;
        $this->mirror->save();
    }
}
