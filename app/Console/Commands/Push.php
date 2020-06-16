<?php

namespace App\Console\Commands;

use App\IssueTracker\Gogs\GogsProject;
use App\Milestone;
use App\Mirror;
use App\Project;
use Illuminate\Console\Command;

class Push extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'it:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push Issues according to sync plan';

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
        foreach (Mirror::onlyClass([Project::class, Milestone::class])->get() as $mirror) {
            // helpdesk.101m.ru -> git.101m.ru
            foreach ($mirror->right->issues as $issue) {
                $mirror->left->server->connect($mirror->user)->pushIssue($issue, $mirror->left);
            }
        }
    }
}
