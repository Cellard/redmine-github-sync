<?php

namespace App\Console\Commands;

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
        \App\Jobs\PushIssues::dispatch();
    }
}
