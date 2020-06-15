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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\Request;
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
}
