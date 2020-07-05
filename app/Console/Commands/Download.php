<?php

namespace App\Console\Commands;

use App\Credential;
use Illuminate\Console\Command;

class Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'it:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download immutable data from known issue trackers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (Credential::whereNotNull('api_key')->get() as $credential) {
            \App\Jobs\Download::dispatch($credential);
        }
    }
}
