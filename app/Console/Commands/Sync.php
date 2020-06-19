<?php

namespace App\Console\Commands;

use App\Issue;
use App\IssueTracker\Contracts\HasDates;
use App\IssueTracker\Contracts\HasLabels;
use App\IssueTracker\Contracts\HasPriority;
use App\IssueTracker\Contracts\HasStatus;
use App\IssueTracker\Contracts\HasTracker;
use App\IssueTracker\Contracts\LabelContract;
use App\Milestone;
use App\Mirror;
use App\Project;
use App\SyncedIssue;
use App\User;
use Carbon\Carbon;
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
        /** @var Mirror $mirror */
        foreach (Mirror::onlyClass([Project::class, Milestone::class])->get() as $mirror) {
            foreach ($mirror->projects() as $project) {
                $issues = $project->server->connect($mirror->user)->getIssues($project->contract());
                foreach ($issues as $remoteIssue) {
                    if ($remoteIssue->id == 33713)
                    {
                        $test = 'asd';
                    }
                    $author = $remoteIssue->author->toLocal($project->server);
                    $issue = Issue::where([
                        'ext_id' => $remoteIssue->id,
                        'project_id' => $project->id
                    ])->orWhereHas('syncedIssues', function ($query) use ($remoteIssue, $project) {
                        $query->where([
                            'ext_id' => $remoteIssue->id,
                            'project_id' => $project->id
                        ]);
                    })->first();
                    
                    if ($issue && $issue->updated_at->lessThan(Carbon::parse($remoteIssue->updated_at))) {
                        $issue->update([
                            'author_id' => $author->id,
                            'subject' => $remoteIssue->subject,
                            'description' => $remoteIssue->description,
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
                        continue;
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
                    $issue->save();
                }
            }
        }
    }



    protected function seed()
    {
        $mirror = new Mirror();

        $left = Project::query()
            ->where('server_id', 'git.101m.ru')
            ->where('slug', 'Cellard/redmine-sync')
            ->first();

        $right = Project::query()
            ->where('server_id', 'helpdesk.101m.ru')
            ->where('slug', 'issue-tracker-syncronizer')
            ->first();

        $mirror->user()->associate(User::query()->find(2));
        $mirror->left()->associate($left);
        $mirror->right()->associate($right);
        $mirror->save();
    }
}
