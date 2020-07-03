<?php

namespace App\Services\Synchronizers;

use App\Issue;
use App\Project;
use Carbon\Carbon;

class ZenitRedmineSynchronizer extends LocalRedmineSynchronizer {

    const MEDIA_GROUP_ID = 171;

    protected function getIssues(Project $project, Carbon $issuesFromUpdatedAtDate): array
    {
        return $this->client->issue->all([
            'project_id' => $project->ext_id,
            'status_id' => '*',
            'assigned_to_id' => self::MEDIA_GROUP_ID,
            'updated_on' => ">={$issuesFromUpdatedAtDate->toIso8601ZuluString()}"
        ])['issues'];
    }

    protected function updateLocalIssue(array $issue, Issue $localIssue): Issue
    {
        $localIssue->update([
            'subject' => $issue['subject'],
            'assignee_id' => $this->mirror->user->id,
            'description' => $issue['description'] ?? null
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
            'assignee_id' => $this->mirror->user->id,
            'subject' => $issue['subject'],
            'description' => $issue['description'] ?? null,
            'updated_at' => Carbon::parse($issue['updated_on'])
        ]);
    }

    protected function updateRemoteIssue(int $id, array $attributes)
    {
        $attributes['assignee_id'] = self::MEDIA_GROUP_ID;
        $this->client->issue->update($id, $attributes);
        return $this->client->issue->show($id)['issue'];
    }

    protected function createRemoteIssue(array $attributes)
    {
        $attributes['assignee_id'] = self::MEDIA_GROUP_ID;
        return (array)$this->client->issue->create($attributes);
    }
}