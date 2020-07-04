<?php

namespace App\Services\Synchronizers;

use Illuminate\Support\Str;
use App\Credential;
use App\Issue;
use App\IssueComment;
use App\IssueFile;
use App\IssueTracker\AccessException;
use App\Project;
use IssueLabelsMapper;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class LocalRedmineSynchronizer {

    protected $client;
    protected $server;
    protected $mirror;

    public function __construct($server, $mirror)
    {
        $this->server = $server;
        $this->mirror = $mirror;
    }

    public function connect(?string $apiKey = null): void
    {
        if (!$apiKey) {
            $apiKey = $this->mirror->user->credentials()->where('server_id', $this->server->id)->first()->api_key;
        }
        $this->client = new \Redmine\Client($this->server->base_uri, $apiKey);
    }

    public function pullIssues(Project $project, Carbon $issuesFromUpdatedAtDate): void
    {
        $this->connect();
        $issues = $this->getIssues($project, $issuesFromUpdatedAtDate);
        foreach ($issues as $issue) {
            try {
                $this->updateOrCreateLocalIssue($issue, $project);
            } catch (\Throwable $th) {
                dump($th->getMessage());
            }
        }
    }

    public function pushIssues(Collection $issuesToPush, Project $project): void
    {
        foreach ($issuesToPush as $localIssue) {
            try {
                dump($localIssue->subject);

                if ($credential = $localIssue->author->credentials()->where('server_id', $this->server->id)->first()) {
                    $this->connect($credential->api_key);
                } else {
                    $this->connect();
                }

                $remoteIssue = $this->updateOrCreateRemoteIssue($localIssue, $project);

                foreach ($localIssue->commentsToPush($project->id)->get() as $comment) {
                    $this->pushComment($comment, $project, $remoteIssue['id']);
                }

                foreach ($localIssue->filesToPush($project->id)->get() as $file) {
                    $this->pushFile($file, $project, $remoteIssue['id']);
                }

            } catch (\Throwable $th) {
                dump($th->getMessage());
            }
        }
    }

    protected function getIssues(Project $project, Carbon $issuesFromUpdatedAtDate): array
    {
        return $this->client->issue->all([
            'project_id' => $project->ext_id,
            'status_id' => '*',
            'updated_on' => ">={$issuesFromUpdatedAtDate->toIso8601ZuluString()}"
        ])['issues'];
    }

    protected function updateOrCreateLocalIssue(array $issue, Project $project): void
    {
        $localIssue = (new Issue)->queryByRemote($issue['id'], $project->id)->first();
        if ($localIssue && $localIssue->updated_at->lessThan(Carbon::parse($issue['updated_on']))) {
            $localIssue = $this->updateLocalIssue($issue, $localIssue);
        } else if (!$localIssue) {
            $localIssue = $this->createLocalIssue($issue, $project);
        } else {
            return;
        }

        $this->attachLabels($localIssue, $issue, $project);
        $this->addComments($issue, $localIssue);
        $this->addFiles($issue, $localIssue);
    }

    protected function attachLabels(Issue $localIssue, array $issue, Project $project): void
    {
        $types = [
            'status',
            'tracker',
            'priority'
        ];
        if ($localIssue->ext_id === $issue['id']) {
            $localIssue->enumerations()->detach();
            foreach ($types as $type) {
                $labelId = IssueLabelsMapper::getLabelByExtId($issue[$type]['id'], $this->server->id, $type);
                $localIssue->enumerations()->attach($labelId);
            }
        } else {
            $labelsMap = $this->mirror->getLabelsMap($project);
            foreach ($types as $type) {
                $label = IssueLabelsMapper::getLabelByExtId($issue[$type]['id'], $this->server->id, $type);
                if ($labelId = IssueLabelsMapper::findIdInLabels($label->id, $labelsMap)) {
                    $localIssue->enumerations()->detach($localIssue->$type()->id);
                    $localIssue->enumerations()->attach($labelId);
                }
            }
        }
    }

    protected function updateOrCreateRemoteIssue(Issue $localIssue, Project $project): array
    {
        $syncedIssue = $project->syncedIssues()->where('issue_id', $localIssue->id)->first();
        $assigne = $localIssue->assignee ? $project->server->credentials()->where('user_id', $localIssue->assignee->id)->first() : null;
        $attributes = [
            'subject' => $localIssue->subject,
            'description' => $localIssue->description,
            'project_id' => $project->ext_id,
            'assigned_to_id' => $assigne['ext_id'] ?? null,
            //'start_date' => $localIssue->started_at ? $localIssue->started_at->toIso8601ZuluString() : null,
            //'due_date' => $localIssue->finished_at ? $localIssue->finished_at->toIso8601ZuluString() : null,
            'author_id' => $this->getAccount()['id']
        ];

        if ($syncedIssue && $syncedIssue->ext_id === $localIssue->ext_id) {
            $attributes['tracker_id'] = $localIssue->tracker()->ext_id;
            $attributes['status_id'] = $localIssue->status()->ext_id;
            $attributes['priority_id'] = $localIssue->priority()->ext_id;
        } else {
            $labelsMap = $this->mirror->getLabelsMap($project);
            if ($labelsMap) {
                if ($ext_id = IssueLabelsMapper::getLabelExtId($localIssue, $labelsMap, 'tracker')) {
                    $attributes['tracker_id'] = $ext_id;
                }
                if ($ext_id = IssueLabelsMapper::getLabelExtId($localIssue, $labelsMap, 'status')) {
                    $attributes['status_id'] = $ext_id;
                }
                if ($ext_id = IssueLabelsMapper::getLabelExtId($localIssue, $labelsMap, 'priority')) {
                    $attributes['priority_id'] = $ext_id;
                }
            }
        }

        if ($syncedIssue) {
            $response = $this->updateRemoteIssue($syncedIssue->ext_id, $attributes);
            $syncedIssue->update([
                'updated_at' => Carbon::parse($response['updated_on'])->setTimezone(config('app.timezone'))
            ]);
        } else {
            $response = $this->createRemoteIssue($attributes);
            $localIssue->syncedIssues()->create([
                'project_id' => $project->id,
                'ext_id' => $response['id'],
                'updated_at' => Carbon::parse($response['updated_on'])->setTimezone(config('app.timezone'))
            ]);
        }
        $localIssue->updated_at = Carbon::parse($response['updated_on'])->setTimezone(config('app.timezone'));
        $localIssue->save();
        return $response;
    }

    protected function updateRemoteIssue(int $id, array $attributes)
    {
        $this->client->issue->update($id, $attributes);
        return $this->client->issue->show($id)['issue'];
    }

    protected function createRemoteIssue(array $attributes)
    {
        return (array)$this->client->issue->create($attributes);
    }

    protected function updateLocalIssue(array $issue, Issue $localIssue): Issue
    {
        $assignee = isset($issue['assigned_to']) ? $this->getUser($issue['assigned_to']['id']) : null;
        $localIssue->update([
            'subject' => $issue['subject'],
            'assignee_id' => $assignee['id'] ?? null,
            'description' => $issue['description'] ?? null
        ]);
        return $localIssue;
    }

    protected function createLocalIssue(array $issue, Project $project): Issue
    {
        $assignee = isset($issue['assigned_to']) ? $this->getUser($issue['assigned_to']['id']) : null;
        $author = $this->getUser($issue['author']['id']);
        return Issue::create([
            'ext_id' => $issue['id'],
            'project_id' => $project->id,
            'author_id' => $author['id'],
            'assignee_id' => $assignee['id'] ?? null,
            'subject' => $issue['subject'],
            'description' => $issue['description'] ?? null,
            'updated_at' => Carbon::parse($issue['updated_on'])
        ]);
    }

    protected function getUser(int $id): User
    {
        $user = $this->client->user->show($id)['user'];
        return $this->updateOrCreateUser($user);
    }

    protected function getAccount(): array
    {
        $response = $this->client->user->getCurrentUser();
        if (isset($response['user'])) {
            return $response['user'];
        } else {
            throw new AccessException("Unauthorized", 403);
        }
    }

    protected function updateOrCreateUser(array $user): User
    {
        if (isset($user['mail'])) {
            $localUser = User::firstOrCreate([
                'email' => $user['mail']
            ], [
                'name' => $user['login'] ?? $user['firstname'] ?? null,
                'email_verified_at' => Carbon::now(),
                'password' => Str::random(64)
            ]);
        } else {
            $localUser = User::create([
                'email' => $user['mail'] ?? null,
                'name' => $user['login'] ?? $user['firstname'] ?? $user['id'] . $this->server->base_url,
                'email_verified_at' => Carbon::now(),
                'password' => Str::random(64)
            ]);
        }

        Credential::updateOrCreate([
            'user_id' => $localUser->id,
            'server_id' => $this->server->id
        ],
        [
            'ext_id' => $user['id'],
            'username' => $user['login'] ?? null
        ]);
        return $localUser;
    }

    protected function getComments(int $id): array
    {
        $comments = [];
        $journals = $this->client->issue->show($id, ['include' => 'journals'])['issue']['journals'];
        foreach ($journals as $item) {
            if ($item['notes']) {
                $item['user'] = $this->getUser($item['user']['id']);
                $comments[] =$item;
            }
        }
        return $comments;
    }

    protected function addComments(array $issue, Issue $localIssue): void
    {
        $comments = $this->getComments($issue['id']);
        foreach ($comments as $comment) {
            if (!(new IssueComment)->queryByExternalId($comment['id'])->first()) {
                $localComment = $localIssue->comments()->create([
                    'body' => $comment['notes'],
                    'ext_id' => $comment['id'],
                    'author_id' => $comment['user']->id,
                    'created_at' => $comment['created_on']
                ]);
                $localComment->syncedComments()->create([
                    'ext_id' => $comment['id'],
                    'project_id' => $localIssue->project_id
                ]);
            }
        }
    }

    protected function pushComment(IssueComment $comment, Project $project, int $issueId): void
    {
        if ($credential = $comment->author->credentials()->where('server_id', $this->server->id)->first()) {
            $this->connect($credential->api_key);
        } else {
            $this->connect();
        }

        try {
            $this->updateRemoteIssue($issueId, ['notes' => $comment->body]);
            $comments = $this->getComments($issueId);
            $comment->syncedComments()->create([
                'ext_id' => end($comments)['id'],
                'project_id' => $project->id
            ]);
        } catch (\Throwable $th) {
            dump($th->getMessage());
        }
    }

    protected function getFiles(int $id): array
    {
        $files = [];
        $attachments = (array)$this->client->issue->show((string)$id, ['include' => 'attachments'])['issue']['attachments'];
        foreach ($attachments as $item) {
            $item['content'] = $this->client->attachment->download($id);
            $item['author'] = $this->getUser($item['author']['id']);
            $files[] = $item;
        }
        return $files;
    }

    protected function addFiles(array $issue, Issue $localIssue): void
    {
        $files = $this->getFiles($issue['id']);
        foreach ($files as $file) {
            $localfile = (new IssueFile)->queryByExternalId($file['id'])->first();
            if ($localfile) {
                $localfile->update([
                    'name' => $file['filename'],
                    'description' => $file['description']
                ]);
            } else {
                $path = '/files/' . $file['filename'];
                Storage::disk('local')->put($path, $file['content']);
                $file = $localIssue->files()->create([
                    'name' => $file['filename'],
                    'description' => $file['description'],
                    'path' => $path,
                    'ext_id' => $file['id'],
                    'author_id' => $file['author']->id,
                    'created_at' => $file['created_on']
                ]);
                $file->syncedFiles()->create([
                    'ext_id' => $file['id'],
                    'project_id' => $localIssue->project->id
                ]);
            }
        }
    }

    protected function pushFile(IssueFile $file, Project $project, int $issueId): void
    {
        if ($credential = $file->author->credentials()->where('server_id', $this->server->id)->first()) {
            $this->connect($credential->api_key);
        } else {
            $this->connect();
        }

        try {
            $response = json_decode($this->client->attachment->upload(Storage::get($file->path)), true);
            $this->client->issue->attach($issueId, [
                'token' => $response['upload']['token'], 
                'filename' => $file->name, 
                'description' => $file->description
            ]);
            $files = $this->getComments($issueId);
            $file->syncedFiles()->updateOrCreate(
                ['ext_id' => end($files)['id']],
                ['project_id' => $project->id]
            );
        } catch (\Throwable $th) {
            dump($th->getMessage());
        }
    }
}