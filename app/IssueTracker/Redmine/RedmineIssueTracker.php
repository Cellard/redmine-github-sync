<?php


namespace App\IssueTracker\Redmine;

use App\IssueTracker\Abstracts\IssueTracker;
use App\IssueTracker\Abstracts\Label;
use App\IssueTracker\AccessException;
use App\IssueTracker\Contracts\IssueContract;
use App\IssueTracker\Contracts\LabelContract;
use App\IssueTracker\Contracts\MilestoneContract;
use App\IssueTracker\Contracts\ProjectContract;
use App\IssueTracker\Contracts\UserContract;
use App\IssueTracker\Contracts\WithPriority;
use App\IssueTracker\Contracts\WithStatus;
use App\IssueTracker\Contracts\WithTracker;
use App\SyncedIssue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RedmineIssueTracker extends IssueTracker implements WithTracker, WithStatus, WithPriority
{

    /**
     * @return \Redmine\Client
     */
    protected function client()
    {
        return new \Redmine\Client($this->getBaseUri(), $this->getApiKey());
    }

    /**
     * @param int $page
     * @return ProjectContract[]|Collection
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Projects
     */
    public function getProjects($page = 1)
    {
        $limit = 100;
        $offset = $this->getOffset($page, $limit);

        $json = (array)$this->client()->project->all([
            'limit' => $limit,
            'offset' => $offset
        ]);

        $projects = [];

        foreach ((array)@$json['projects'] as $value) {
            $projects[] = RedmineProject::createFromRemote($value, $this->getBaseUri());
        }

        return collect($projects);
    }

    /**
     * @param ProjectContract $project
     * @return MilestoneContract[]|Collection
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Versions
     */
    public function getMilestones(ProjectContract $project)
    {
        $json = (array)$this->client()->version->all($project->id);

        $milestones = [];

        foreach ((array)@$json['versions'] as $value) {
            if ($value['status'] == 'open') {
                $milestones[] = RedmineMilestone::createFromRemote($value, $project);
            }
        }

        return collect($milestones);
    }

    /**
     * Get project priority listing
     * @param ProjectContract $project
     * @return Collection|LabelContract[]
     */
    public function getPriorityListing(ProjectContract $project)
    {
        $json = (array)$this->client()->issue_priority->all();

        $issue_priorities = [];

        foreach ((array)@$json['issue_priorities'] as $value) {
            $issue_priorities[] = Label::createFromRemote($value, $project);
        }

        return collect($issue_priorities);
    }

    /**
     * Get project status listing
     * @param ProjectContract $project
     * @return Collection|LabelContract[]
     */
    public function getStatusListing(ProjectContract $project)
    {
        $json = (array)$this->client()->issue_status->all();

        $issue_statuses = [];

        foreach ((array)@$json['issue_statuses'] as $value) {
            $value['more'] = isset($value['is_closed']) ? ['is_closed' => $value['is_closed']] : null;
            $issue_statuses[] = Label::createFromRemote($value, $project);
        }

        return collect($issue_statuses);
    }

    /**
     * Get project tracker listing
     * @param ProjectContract $project
     * @return Collection|LabelContract[]
     */
    public function getTrackerListing(ProjectContract $project)
    {
        $json = (array)$this->client()->tracker->all();

        $trackers = [];

        foreach ((array)@$json['trackers'] as $value) {
            $trackers[] = Label::createFromRemote($value, $project);
        }

        return collect($trackers);
    }

    /**
     * Get current user
     * @return UserContract
     * @throws AccessException
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Users#usersidformat
     */
    public function getAccount()
    {
        $json = (array)$this->client()->user->getCurrentUser();
        if (isset($json['user'])) {
            return RedmineUser::createFromRemote($json['user']);
        } else {
            throw new AccessException("Unauthorized", 403);
        }
    }

    /**
     * @param $id
     * @return RedmineUser
     */
    public function getUser($id)
    {
        $json = (array)$this->client()->user->show($id);
        unset($json['user']['memberships']);
        unset($json['user']['groups']);
        return RedmineUser::createFromRemote((array)@$json['user']);
    }

    /**
     * Get project issue listing
     * @param ProjectContract $project
     * @return Collection|IssueContract[]
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Issues
     */
    public function getIssues(ProjectContract $project)
    {
        $json = (array)$this->client()->issue->all(['project_id' => $project->id]);
        $issues = [];

        foreach ((array)@$json['issues'] as $value) {
            $value['author'] = $this->getUser($value['author']['id'])->toArray();
            if (isset($value['assigned_to'])) {
                $value['assigned_to'] = $this->getUser($value['assigned_to']['id'])->toArray();
            }
            $issues[] = RedmineIssue::createFromRemote($value, $project);
        }

        return collect($issues);
    }

    /**
     * Create or update issue in redmine
     *
     * @param \App\Issue $issue
     * @param \App\Project $project
     * @return array
     */
    public function pushIssue(\App\Issue $issue, \App\Project $project)
    {
        $syncedIssue = $project->syncedIssues()->where('issue_id', $issue->id)->first();
        $assigneId = $project->server->credentials()->where('user_id', $issue->assignee->id)->first()['ext_id'];
        $attributes = [
            'subject' => $issue->subject,
            'description' => $issue->description,
            'project_id' => $project->ext_id,
            'assigned_to_id' => $assigneId,
            'author_id' => $this->getAccount()->id
        ];

        if ($syncedIssue) {
            $result = $this->updateIssue($syncedIssue->ext_id, $attributes);
        } else {
            $result = $this->createIssue($attributes);
            SyncedIssue::create([
                'issue_id' => $issue->id,
                'project_id' => $project->id,
                'ext_id' => $result['id'],
                'updated_at' => $result['updated_on']
            ]);
        }
        $issue->updated_at = $result['updated_on'];
        $issue->save();
        $issue->syncedIssues()->update(['updated_at' => $issue->updated_at]);
        return $result;
    }

    /**
     * Create issue in redmine
     *
     * @param array $attributes
     * @return array
     */
    protected function createIssue(array $attributes): array
    {
        $response = $this->client()->issue->create($attributes);
        return (array)$response;
    }

    /**
     * Update issue in redmine
     *
     * @param integer $id
     * @param array $attributes
     * @return array
     */
    protected function updateIssue(int $id, array $attributes): array
    {
        $this->client()->issue->update($id, $attributes);
        $response = $this->client()->issue->show($id)['issue'];
        return (array)$response;
    }
}
