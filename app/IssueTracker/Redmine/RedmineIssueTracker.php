<?php


namespace App\IssueTracker\Redmine;

use App\IssueTracker\Abstracts\IssueTracker;
use App\IssueTracker\Abstracts\Label;
use App\IssueTracker\Redmine\RedmineIssueComment;
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
use Illuminate\Support\Facades\Storage;

class RedmineIssueTracker extends IssueTracker implements WithTracker, WithStatus, WithPriority
{
    /**
     * @var \Redmine\Client
     */
    protected $client;

    public function __construct($baseUri, $apiKey)
    {
        parent::__construct($baseUri, $apiKey);
        $this->client = $this->client();
    }

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

        $json = (array)$this->client->project->all([
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
        $json = (array)$this->client->version->all($project->id);

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
        $json = (array)$this->client->issue_priority->all();

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
        $json = (array)$this->client->issue_status->all();

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
        $json = (array)$this->client->tracker->all();

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
        $json = (array)$this->client->user->getCurrentUser();
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
        $json = (array)$this->client->user->show($id);
        unset($json['user']['memberships']);
        unset($json['user']['groups']);
        return RedmineUser::createFromRemote((array)@$json['user']);
    }

    /**
     * @param $id
     * @return 
     */
    public function getComments($id)
    {
        $comments = [];
        $journals = (array)$this->client->issue->show((string)$id, ['include' => 'journals'])['issue']['journals'];
        foreach ($journals as $item) {
            if ($item['notes']) {
                $item['user'] = $this->getUser($item['user']['id'])->toArray();
                $comments[] = RedmineIssueComment::createFromRemote($item);
            }
        }
        return $comments;
    }

    /**
     * @param $id
     * @return 
     */
    public function getFiles($id)
    {
        $files = [];
        $attachments = (array)$this->client->issue->show((string)$id, ['include' => 'attachments'])['issue']['attachments'];
        foreach ($attachments as $item) {
            $item['content'] = $this->client->attachment->download($id);
            $item['author'] = $this->getUser($item['author']['id'])->toArray();
            $files[] = RedmineIssueFile::createFromRemote($item);
        }
        return $files;
    }

    protected function downloadFile($id)
    {
        return $this->client->attachment->download($id);
    }

    /**
     * Get project issue listing
     * @param ProjectContract $project
     * @param Carbon $updatedDateTime
     * @return Collection|IssueContract[]
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Issues
     */
    public function getIssues(ProjectContract $project, Carbon $updatedDateTime)
    {
        $params = [
            'project_id' => $project->id,
            'status_id' => '*'
        ];
        if ($updatedDateTime) {
            $params['updated_on'] = ">={$updatedDateTime->toIso8601ZuluString()}";
        }
        $json = (array)$this->client->issue->all($params);
        $issues = (array)$json['issues'] ?? [];

        foreach ($issues as $key => $value) {
            $value['author'] = $this->getUser($value['author']['id'])->toArray();
            $value['comments'] = $this->getComments($value['id']);
            $value['files'] = $this->getFiles($value['id']);
            if (isset($value['assigned_to'])) {
                $value['assigned_to'] = $this->getUser($value['assigned_to']['id'])->toArray();
            }
            $issues[$key] = RedmineIssue::createFromRemote($value, $project);
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
    public function pushIssue(\App\Issue $issue, \App\Project $project, ?array $labelsMap)
    {
        $syncedIssue = $project->syncedIssues()->where('issue_id', $issue->id)->first();
        $assigneId = $issue->assignee ? $project->server->credentials()->where('user_id', $issue->assignee->id)->first()['ext_id'] : null;
        $attributes = [
            'subject' => $issue->subject,
            'description' => $issue->description,
            'project_id' => $project->ext_id,
            'assigned_to_id' => $assigneId,
            'author_id' => $this->getAccount()->id,
        ];

        if ($syncedIssue && $syncedIssue->ext_id === $issue->ext_id) {
            $attributes['tracker_id'] = $issue->tracker()->ext_id;
            $attributes['status_id'] = $issue->status()->ext_id;
            $attributes['priority_id'] = $issue->priority()->ext_id;
        } else {
            if ($labelsMap) {
                if ($ext_id = $this->getLabelExtId($issue, $labelsMap, 'tracker')) {
                    $attributes['tracker_id'] = $ext_id;
                }
                if ($ext_id = $this->getLabelExtId($issue, $labelsMap, 'status')) {
                    $attributes['status_id'] = $ext_id;
                }
                if ($ext_id = $this->getLabelExtId($issue, $labelsMap, 'priority')) {
                    $attributes['priority_id'] = $ext_id;
                }
            }
        }

        if ($syncedIssue) {
            $result = $this->updateIssue($syncedIssue->ext_id, $attributes);
        } else {
            $result = $this->createIssue($attributes);
            SyncedIssue::create([
                'issue_id' => $issue->id,
                'project_id' => $project->id,
                'ext_id' => $result['id'],
                'updated_at' => Carbon::parse($result['updated_on'])->setTimezone(config('app.timezone'))
            ]);
        }
        $issue->updated_at = Carbon::parse($result['updated_on'])->setTimezone(config('app.timezone'));
        $issue->save();
        $issue->syncedIssues()->update(['updated_at' => $issue->updated_at]);
        return $result;
    }

    public function pushComment($comment, $remoteIssueId)
    {
        $this->updateIssue($remoteIssueId, ['notes' => $comment->body]);
        $comments = $this->getComments($remoteIssueId);
        return end($comments);
    }

    public function pushFile($file, $remoteIssueId)
    {
        $response = json_decode($this->client->attachment->upload(Storage::get($file->path)), true);
        $this->client->issue->attach($remoteIssueId, [
            'token' => $response['upload']['token'], 
            'filename' => $file->name, 
            'description' => $file->description
        ]);
        $files = $this->getFiles($remoteIssueId);
        return end($files);
    }

    /**
     * Get external matched label id
     *
     * @param \App\Issue $issue
     * @param array $labelsMap
     * @param string $type
     * @return integer|null
     */
    protected function getLabelExtId(\App\Issue $issue, array $labelsMap, string $type)
    {
        $label = $issue->enumerations()->where('type', $type)->first();
        if ($label) {
            $label = $this->findInLabels($label->id, $labelsMap);
            if ($label) {
                return \App\Label::where([
                    'id' => $label['right_label_id'],
                    'type' => $type
                ])->first()->ext_id;
            }
        }
        return null;
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

    /**
     * Create issue in redmine
     *
     * @param array $attributes
     * @return array
     */
    protected function createIssue(array $attributes): array
    {
        $response = $this->client->issue->create($attributes);
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
        $this->client->issue->update($id, $attributes);
        $response = $this->client->issue->show($id)['issue'];
        return (array)$response;
    }
}
