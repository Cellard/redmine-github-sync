<?php

namespace App\Console\Commands;

use App\Issue;
use App\IssueComment;
use App\IssueFile;
use App\IssueTracker\Contracts\HasDates;
use App\IssueTracker\Contracts\HasLabels;
use App\IssueTracker\Contracts\HasPriority;
use App\IssueTracker\Contracts\HasStatus;
use App\IssueTracker\Contracts\HasTracker;
use App\IssueTracker\Contracts\LabelContract;
use App\Milestone;
use App\Mirror;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
        /** @var Mirror $mirror */
        foreach (Mirror::onlyClass([Project::class, Milestone::class])->get() as $mirror) {
            
            $syncedAt = Carbon::now()->subMinutes(5);

            foreach ($mirror->projects() as $project) {
                $issues = $project->server->connect($mirror->user)->getIssues($project->contract(), $mirror->synced_at ?? $mirror->created_at);
                foreach ($issues as $remoteIssue) {
                    try {
                        $this->updateOrCreateIssue($remoteIssue, $project);
                    } catch (\Throwable $th) {
                        $this->error($th->getMessage());
                    }
                }
            }

            $mirror->synced_at = $syncedAt;
            $mirror->save();
        }
    }

    protected function updateOrCreateIssue($remoteIssue, $project)
    {
        $author = $remoteIssue->author->toLocal($project->server);
        $issue = (new Issue)->queryByRemote($remoteIssue->id, $project->id)->first();
        
        if ($issue && $issue->updated_at->lessThan(Carbon::parse($remoteIssue->updated_at))) {
            $issue->update([
                'author_id' => $author->id,
                'subject' => $remoteIssue->subject,
                'description' => $remoteIssue->description
            ]);
        } else if (!$issue) {
            $issue = Issue::create([
                'ext_id' => $remoteIssue->id,
                'project_id' => $project->id,
                'author_id' => $author->id,
                'subject' => $remoteIssue->subject,
                'description' => $remoteIssue->description
            ]);
        } else {
            return null;
        }

        if (count($remoteIssue->comments))
        {
            $this->info('comments');
            $this->attachComments($issue, $remoteIssue->comments, $project);
        }

        if (count($remoteIssue->files))
        {
            $this->info('files');
            $this->attachFiles($issue, $remoteIssue->files, $project);
        }

        if ($remoteIssue->milestone) {
            $issue->milestone()->associate($remoteIssue->milestone->toLocal($project));
        }

        if ($remoteIssue->assignee) {
            $issue->assignee()->associate($remoteIssue->assignee->toLocal($project->server));
        }

        if ($remoteIssue instanceof HasDates) {
            $issue->started_at = $remoteIssue->started_at;
            $issue->finished_at = $remoteIssue->finished_at;
        }

        if ($issue->ext_id == $remoteIssue['id']) {
            $this->attachLabels($issue, $remoteIssue, $project);
        } else {
            $this->attachLabelsByLabelsMap($issue, $remoteIssue, $project);
        }

        return $issue->save();
    }

    protected function attachLabels($issue, $remoteIssue, $project)
    {
        $issue->enumerations()->detach();

        switch (true) {
            case $remoteIssue instanceof HasLabels:
                $remoteIssue->labels->map(function(LabelContract $label) use($issue, $project) {
                    $issue->enumerations()->attach($label->toLocal($project->server));
                });
                break;
            case $remoteIssue instanceof HasStatus:
                $issue->enumerations()->attach($remoteIssue->status->toLocal($project->server, 'status'));
            case $remoteIssue instanceof HasTracker:
                $issue->enumerations()->attach($remoteIssue->tracker->toLocal($project->server, 'tracker'));
            case $remoteIssue instanceof HasPriority:
                $issue->enumerations()->attach($remoteIssue->priority->toLocal($project->server, 'priority'));

            default:
                break;
        }
    }

    protected function attachLabelsByLabelsMap($issue, $remoteIssue, $project)
    {
        $issue->enumerations()->detach();

        switch (true) {
            case $remoteIssue instanceof HasStatus:
                $status = $remoteIssue->status->toLocal($project->server, 'status');
                $status = $this->findInLabels($status->id, $project->labelsMap);
                if ($status) {
                    $issue->enumerations()->attach($status['right_label_id']);
                }
            case $remoteIssue instanceof HasTracker:
                $tracker = $remoteIssue->tracker->toLocal($project->server, 'tracker');
                $tracker = $this->findInLabels($tracker->id, $project->labelsMap);
                if ($tracker) {
                    $issue->enumerations()->attach($tracker['right_label_id']);
                }
            case $remoteIssue instanceof HasPriority:
                $priority = $remoteIssue->priority->toLocal($project->server, 'priority');
                $priority = $this->findInLabels($priority->id, $project->labelsMap);
                if ($priority) {
                    $issue->enumerations()->attach($priority['right_label_id']);
                }

            default:
                break;
        }
    }

    /**
     * Find matched label in labels map
     *
     * @param integer $id
     * @param array $labelsMap
     * @return array|null $item
     */
    protected function findInLabels(int $id, array $labelsMap)
    {
        foreach ($labelsMap as $item) {
            if ($item['left_label_id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    protected function attachComments($issue, array $remoteComments, $project)
    {
        foreach ($remoteComments as $remoteComment) {
            if (IssueComment::where('ext_id', $remoteComment['id'])->orWhereHas('syncedComments', function($query) use ($remoteComment) {
                $query->where('ext_id', $remoteComment['id']);
            })->first()) {
                continue;
            }
            
            $author = $remoteComment->author->toLocal($issue->project->server);
            $comment = $issue->comments()->create([
                'body' => $remoteComment['notes'],
                'ext_id' => $remoteComment['id'],
                'author_id' => $author->id,
                'created_at' => $remoteComment['created_on']
            ]);
            $comment->syncedComments()->create([
                'ext_id' => $remoteComment['id'],
                'project_id' => $project->id
            ]);
        }
    }

    protected function attachFiles($issue, array $remoteFiles, $project)
    {
        foreach ($remoteFiles as $remoteFile) {
            $file = IssueFile::where('ext_id', $remoteFile['id'])->orWhereHas('syncedFiles', function($query) use ($remoteFile) {
                $query->where('ext_id', $remoteFile['id']);
            })->first();
            if ($file) {
                $file->update([
                    'name' => $remoteFile['name'],
                    'description' => $remoteFile['description']
                ]);
            } else {
                $path = '/files/' . $remoteFile['name'];
                Storage::disk('local')->put($path, $remoteFile['content']);
                $author = $remoteFile->author->toLocal($issue->project->server);
                $file = $issue->files()->create([
                    'name' => $remoteFile['name'],
                    'description' => $remoteFile['description'],
                    'path' => $path,
                    'ext_id' => $remoteFile['id'],
                    'author_id' => $author->id,
                    'created_at' => $remoteFile['created_on']
                ]);
                $file->syncedFiles()->create([
                    'ext_id' => $remoteFile['id'],
                    'project_id' => $project->id
                ]);
            }
        }
    }
}
