<?php

namespace App\Services\Synchronizers;

use App\Issue;
use App\IssueComment;
use App\Project;
use Carbon\Carbon;

class ZenitRedmineSynchronizer extends LocalRedmineSynchronizer {

    const MEDIA_GROUP_ID = 171;

    protected function getIssues(Project $project, ?Carbon $issuesUpdatedAtDate, ?Carbon $issuesCreatedAtDate): array
    {
        $offset = 0;
        $totalCount = 1;
        $issues = [];
        while ($totalCount > count($issues)) {
            $params = [
                'offset' => $offset,
                'project_id' => $project->ext_id,
                'status_id' => '*',
                'assigned_to_id' => self::MEDIA_GROUP_ID,
            ];

            if ($issuesUpdatedAtDate) {
                $params['updated_on'] = ">={$issuesUpdatedAtDate->toIso8601ZuluString()}";
            }
            if ($issuesCreatedAtDate) {
                $params['created_on'] = ">={$issuesCreatedAtDate->toIso8601ZuluString()}";
            }
            $response = $this->client->issue->all($params);
            $offset += $response['limit'];
            $totalCount = $response['total_count'];
            $issues = array_merge($issues, $response['issues']);
        }
        return $issues;
    }

    protected function updateLocalIssue(array $issue, Issue $localIssue): Issue
    {
        $localIssue->update([
            'subject' => $issue['subject'],
            'assignee_id' => $this->mirror->owner_id,
            'estimated_hours' => $issue['estimated_hours'] ?? null,
            'done_ratio' => $issue['done_ratio'] ?? null,
            'description' => $issue['description'] ?? null,
            'started_at' => isset($issue['start_date']) 
                ? Carbon::parse($issue['start_date'])->setTimezone(config('app.timezone'))
                : null,
            'finished_at' => isset($issue['due_date']) 
                ? Carbon::parse($issue['due_date'])->setTimezone(config('app.timezone')) 
                : null,
            'updated_at' => Carbon::parse($issue['updated_on'])->setTimezone(config('app.timezone'))
        ]);
        return $localIssue;
    }

    protected function createLocalIssue(array $issue, Project $project): Issue
    {
        $author = $this->getUser($issue['author']['id']);
        return Issue::create([
            'ext_id' => $issue['id'],
            'project_id' => $project->id,
            'author_id' => $author['id'],
            'assignee_id' => $this->mirror->owner_id,
            'subject' => $issue['subject'],
            'estimated_hours' => $issue['estimated_hours'] ?? null,
            'done_ratio' => $issue['done_ratio'] ?? null,
            'description' => $issue['description'] ?? null,
            'started_at' => isset($issue['start_date']) 
                ? Carbon::parse($issue['start_date'])->setTimezone(config('app.timezone'))
                : null,
            'finished_at' => isset($issue['due_date']) 
                ? Carbon::parse($issue['due_date'])->setTimezone(config('app.timezone')) 
                : null,
            'updated_at' => Carbon::parse($issue['updated_on'])
        ]);
    }

    protected function updateRemoteIssue(int $id, array $attributes)
    {
        $attributes['assigned_to_id'] = self::MEDIA_GROUP_ID;
        $this->client->issue->update($id, $attributes);
        return $this->client->issue->show($id)['issue'];
    }

    protected function createRemoteIssue(array $attributes)
    {
        $attributes['assigned_to_id'] = self::MEDIA_GROUP_ID;
        return (array)$this->client->issue->create($attributes);
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
}