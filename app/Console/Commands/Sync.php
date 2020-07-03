<?php

namespace App\Console\Commands;

use App\Jobs\PullIssues;
use App\Jobs\PushIssues;
use Illuminate\Console\Command;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'it:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Issues';

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
        PullIssues::withChain([
            function ()
            {
                dump('next');
            },
            new PushIssues
        ])->dispatch();
    }
}
