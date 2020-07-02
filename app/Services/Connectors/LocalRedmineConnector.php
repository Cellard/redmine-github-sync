<?php

namespace App\Services\Connectors;

use Illuminate\Support\Str;
use App\Credential;
use App\Issue;
use App\IssueComment;
use App\IssueFile;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LocalRedmineConnector {

    private $client;
    private $server;

    public function connect($url, $apiKey)
    {
        $this->client = new \Redmine\Client($url, $apiKey);
    }

    public function pullIssues($project, $issuesFromUpdatedAtDate)
    {
        $this->setServer($project->server);
        $issues = $this->getIssues($project, $issuesFromUpdatedAtDate);
        foreach ($issues as $issue) {
            try {
                $this->updateOrCreateIssue($issue, $project);
            } catch (\Throwable $th) {
                dump($th->getMessage());
            }
        }
    }

    private function setServer($server)
    {
        $this->server = $server;
    }

    private function getIssues($project, $issuesFromUpdatedAtDate)
    {
        return $this->client->issue->all([
            'project_id' => $project->ext_id,
            'status_id' => '*',
            'updated_on' => ">={$issuesFromUpdatedAtDate->toIso8601ZuluString()}"
        ])['issues'];
    }

    private function updateOrCreateIssue($issue, $project)
    {
        $localIssue = (new Issue)->queryByRemote($issue['id'], $project->id)->first();
        if ($localIssue && $localIssue->updated_at->lessThan(Carbon::parse($issue['updated_on']))) {
            $localIssue = $this->updateIssue($issue, $localIssue);
        } else if (!$localIssue) {
            $localIssue = $this->createIssue($issue, $project);
        } else {
            return;
        }
        $this->addComments($issue, $localIssue);
        $this->addFiles($issue, $localIssue);
    }

    private function updateIssue($issue, $localIssue)
    {
        $assignee = isset($issue['assigned_to']) ? $this->getUser($issue['assigned_to']['id']) : null;
        $localIssue->update([
            'subject' => $issue['subject'],
            'assignee_id' => $assignee['id'] ?? null,
            'description' => $issue['description']
        ]);
        return $localIssue;
    }

    private function createIssue($issue, $project)
    {
        $assignee = isset($issue['assigned_to']) ? $this->getUser($issue['assigned_to']['id']) : null;
        $author = $this->getUser($issue['author']['id']);
        return Issue::create([
            'ext_id' => $issue['id'],
            'project_id' => $project->id,
            'author_id' => $author['id'],
            'assignee_id' => $assignee['id'] ?? null,
            'subject' => $issue['subject'],
            'description' => $issue['description'],
            'updated_at' => Carbon::parse($issue['updated_on'])
        ]);
    }

    private function getUser($id)
    {
        $user = $this->client->user->show($id)['user'];
        return $this->updateOrCreateUser($user);
    }

    private function updateOrCreateUser($user)
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

    private function getComments($id)
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

    private function addComments($issue, $localIssue)
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

    private function getFiles($id)
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

    private function addFiles($issue, $localIssue)
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
}