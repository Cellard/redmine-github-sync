<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PullIssues extends Command
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
        \App\Jobs\PullIssues::dispatch();
    }
}
