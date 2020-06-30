<?php

namespace App\Console\Commands;

use App\Credential;
use App\IssueTracker\AccessException;
use App\IssueTracker\Contracts\IssueTrackerInterface;
use App\IssueTracker\Contracts\ProjectContract;
use App\IssueTracker\Contracts\WithLabels;
use App\IssueTracker\Contracts\WithPriority;
use App\IssueTracker\Contracts\WithStatus;
use App\IssueTracker\Contracts\WithTracker;
use App\Label;
use App\Milestone;
use App\Project;
use App\Server;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
     * @var IssueTrackerInterface
     */
    protected $connector;

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
        $this->downloadProjects();
    }

    /**
     * Обновляет информацию о проектах, скачивает их в локальную б/д
     * @throws \App\Exceptions\DriverNotSupportedException
     */
    protected function downloadProjects()
    {
        $this->info("Downloading projects...");

        /** @var Credential $credential */
        foreach (Credential::all() as $credential) {
            $this->connector = $credential->server->connect($credential->user);

            try {
                $account = $this->connector->getAccount();
                $credential->ext_id = $account->id;
                $credential->error = null;
                $credential->save();
            } catch (\Throwable $e) {
                $credential->ext_id = null;
                $credential->error = "{$e->getCode()}:{$e->getMessage()}";
                $credential->save();
                $this->error("Server [{$credential->server->id}] denies user [{$credential->user->email}]");
                continue;
            }

            $this->info("Server [{$credential->server->id}] projects:");

            $sync = [];

            $credential->user->projects($credential->server)->detach();
            $credential->user->milestones($credential->server)->detach();

            /** @var ProjectContract $remote */
            foreach ($this->connector->getProjects() as $remote) {

                $project = $remote->toLocal($credential->server);

                $sync[] = $project->id;
                $this->info("\t" . $remote->slug);

                $this->importLabelListing($project);
                $this->importTrackerListing($project);
                $this->importStatusListing($project);
                $this->importPriorityListing($project);

                $credential->user->projects()->attach($project->id);

                $credential->user->milestones()->attach(
                    $this->importMilestones($project)
                );

            }
        }
    }

    protected function importMilestones(Project $project)
    {
        $sync = [];

        foreach ($this->connector->getMilestones($project->contract()) as $remote) {

            /** @var Milestone $milestone */
            $milestone = $remote->toLocal($project);
            $sync[] = $milestone->id;

            $this->info("\t\t" . $remote->name);
        }

        return $sync;
    }

    protected function importLabels(Collection $labels, Project $project, $type = null)
    {
        $sync = [];

        /** @var \App\IssueTracker\Abstracts\Label $label */
        foreach ($labels as $label) {
            $sync[] = $label->toLocal($project->server, $type)->getKey();
        }

        $delete = $project->enumerations()->whereNotIn('id', $sync);
        if ($type) {
            $delete->where('type', $type);
        }
        $delete->delete();

        return $sync;
    }

    protected function importLabelListing(Project $project)
    {
        if ($this->connector instanceof WithLabels) {
            return $this->importLabels($this->connector->getLabelListing($project->contract()), $project);
        }
    }

    protected function importTrackerListing(Project $project)
    {
        if ($this->connector instanceof WithTracker) {
            return $this->importLabels($this->connector->getTrackerListing($project->contract()), $project, Label::TRACKER);
        }
    }

    protected function importStatusListing(Project $project)
    {
        if ($this->connector instanceof WithStatus) {
            return $this->importLabels($this->connector->getStatusListing($project->contract()), $project, Label::STATUS);
        }
    }

    protected function importPriorityListing(Project $project)
    {
        if ($this->connector instanceof WithPriority) {
            return $this->importLabels($this->connector->getPriorityListing($project->contract()), $project, Label::PRIORITY);
        }
    }


}
