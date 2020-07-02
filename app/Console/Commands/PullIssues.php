<?php

namespace App\Console\Commands;

use App\Milestone;
use App\Mirror;
use App\Project;
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
                $connector = (new ConnectorFactory)->make($project->server, $mirror->user);
                $connector->pullIssues($project, $mirror->synced_at ?? $mirror->created_at);
            }

            $mirror->synced_at = $syncedAt;
            $mirror->save();
        }
    }
}
