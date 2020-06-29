<?php

namespace App\Console\Commands;

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
        $syncingMap = $this->createSyncingMap();

        foreach ($syncingMap as $syncingItem) {
            foreach ($syncingItem['issues'] as $issue) {
                $connection = $syncingItem['project']->server->connect($issue->author);
                try {
                    $connection->pushIssue($issue, $syncingItem['project'], $syncingItem['labelsMap']);
                } catch (\Throwable $th) {
                    $this->error($th->getMessage());
                }
            }
        }
    }

    /**
     * Return map of projects and issues to push
     *
     * @return array
     */
    protected function createSyncingMap(): array
    {
        $syncingMap = [];
        $mirrors = Mirror::onlyClass([Project::class, Milestone::class])->get();
        foreach ($mirrors as $mirror) {
            $syncingMap[] = [
                'project' => $mirror->left,
                'issues' => $mirror->config === 'ltr' ? [] : $mirror->left->issuesToPush($mirror->right)->get(),
                'labelsMap' => $mirror->ltr_labels
            ];
            $syncingMap[] = [
                'project' => $mirror->right,
                'issues' => $mirror->config === 'rtl' ? [] : $mirror->right->issuesToPush($mirror->left)->get(),
                'labelsMap' => $mirror->rtl_labels
            ];
        }
        return $syncingMap;
    }
}
