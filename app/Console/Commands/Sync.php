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
use App\User;
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
                foreach ($issues as $remote) {


                    $author = $remote->author->toLocal($project->server);

                    /** @var Issue $issue */
                    $issue = Issue::query()->updateOrCreate([
                        'ext_id' => $remote->id,
                        'project_id' => $project->id
                    ], [
                        'author_id' => $author->id,
                        'subject' => $remote->subject,
                        'description' => $remote->description,
                        'created_at' => $remote->created_at
                    ]);

                    if ($remote->milestone) {
                        $issue->milestone()->associate($remote->milestone->toLocal($project));
                    }

                    if ($remote->assignee) {
                        $issue->assignee()->associate($remote->assignee->toLocal($project->server));
                    }

                    if ($remote instanceof HasDates) {
                        $issue->started_at = $remote->started_at;
                        $issue->finished_at = $remote->finished_at;
                    }

                    $issue->enumerations()->detach();

                    // Github, Gogs
                    if ($remote instanceof HasLabels) {
                        $remote->labels->map(function(LabelContract $label) use($issue, $project) {
                            $issue->enumerations()->attach($label->toLocal($project));
                        });
                    }

                    // Redmine
                    if ($remote instanceof HasStatus) {
                        $issue->enumerations()->attach($remote->status->toLocal($project));
                    }
                    if ($remote instanceof HasTracker) {
                        $issue->enumerations()->attach($remote->tracker->toLocal($project));
                    }
                    if ($remote instanceof HasPriority) {
                        $issue->enumerations()->attach($remote->priority->toLocal($project));
                    }

                    $issue->updated_at = $remote->updated_at;

                    $issue->save();

                    //dd($issue->toArray());
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

        $mirror->user()->associate(User::query()->find(1));
        $mirror->left()->associate($left);
        $mirror->right()->associate($right);
        $mirror->save();
    }
}
