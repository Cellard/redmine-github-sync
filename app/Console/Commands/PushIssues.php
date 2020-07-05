<?php

namespace App\Console\Commands;

use App\Project;
use App\Milestone;
use App\Mirror;
use Illuminate\Console\Command;

class PushIssues extends Command
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
    protected $description = 'Push Issues';

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
            \App\Jobs\PushIssues::dispatch($mirror);
        }
    }
}
