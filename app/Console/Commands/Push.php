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
                $project = $syncingItem['project'];
                $mirror = $syncingItem['mirror'];
                $labelsMap = $syncingItem['labelsMap'];
                if ($issue->author->credentials()->where('server_id', $project->server_id)->first()) {
                    $connection = $project->server->connect($issue->author);
                } else {
                    $connection = $project->server->connect($mirror->user);
                }
                try {
                    $result = $connection->pushIssue($issue, $project, $labelsMap);
                } catch (\Throwable $th) {
                    $this->error($th->getMessage());
                }
                foreach ($issue->commentsToPush($project->id)->get() as $comment) {
                    $this->pushComment($comment, $project, $result['id'], $mirror);
                }

                foreach ($issue->filesToPush($project->id)->get() as $file) {
                    $this->pushFile($file, $project, $result['id'], $labelsMap);
                }
            }
        }
    }

    protected function pushComment($comment, $project, $issueId, $mirror)
    {
        if ($comment->author->credentials()->where('server_id', $project->server_id)->first()) {
            $connection = $project->server->connect($comment->author);
        } else {
            $connection = $project->server->connect($mirror->user);
        }
        try {
            $result = $connection->pushComment($comment, $issueId);
            $comment->syncedComments()->create([
                'ext_id' => $result['id'],
                'project_id' => $project->id
            ]);
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    protected function pushFile($file, $project, $issueId, $mirror)
    {
        if ($file->author->credentials()->where('server_id', $project->server_id)->first()) {
            $connection = $project->server->connect($file->author);
        } else {
            $connection = $project->server->connect($mirror->user);
        }
        try {
            $result = $connection->pushFile($file, $issueId);
            $file->syncedFiles()->updateOrCreate(
                ['ext_id' => $result['id']],
                ['project_id' => $project->id]
            );
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
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
                'issues' => $mirror->config === 'ltr' ? $mirror->left->issuesToPush()->get() : $mirror->left->issuesToPush($mirror->right)->get(),
                'labelsMap' => $mirror->rtl_labels,
                'mirror' => $mirror
            ];
            $syncingMap[] = [
                'project' => $mirror->right,
                'issues' => $mirror->config === 'rtl' ? $mirror->right->issuesToPush()->get() : $mirror->right->issuesToPush($mirror->left)->get(),
                'labelsMap' => $mirror->ltr_labels,
                'mirror' => $mirror
            ];
        }
        return $syncingMap;
    }
}
