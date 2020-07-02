<?php

namespace App\Console\Commands;

use App\Milestone;
use App\Mirror;
use App\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'it:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull Issues';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        /** @var Mirror $mirror */
        foreach (Mirror::onlyClass([Project::class, Milestone::class])->get() as $mirror) {
            
            $syncedAt = Carbon::now()->subMinutes(5);

            foreach ($mirror->projects() as $project) {
                \App\Jobs\PullIssues::dispatch($project, $mirror);
            }

            //$mirror->synced_at = $syncedAt;
            $mirror->save();
        }
    }
}
