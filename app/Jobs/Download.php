<?php

namespace App\Jobs;

use App\Credential;
use App\Factories\DownloaderFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Download implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $credential;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Credential $credential)
    {
        $this->credential = $credential;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $downloader = (new DownloaderFactory)->make($this->credential);
        $downloader->download();
    }
}
