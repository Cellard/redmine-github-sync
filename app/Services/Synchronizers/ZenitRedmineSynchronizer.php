<?php

namespace App\Services\Synchronizers;

use App\Issue;
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
            'assignee_id' => $this->mirror->user->id,
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
            'assignee_id' => $this->mirror->user->id,
            'subject' => $issue['subject'],
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
        $attributes['assignee_id'] = self::MEDIA_GROUP_ID;
        return[];
        $this->client->issue->update($id, $attributes);
        return $this->client->issue->show($id)['issue'];
    }

    protected function createRemoteIssue(array $attributes)
    {
        $attributes['assignee_id'] = self::MEDIA_GROUP_ID;
        return [];
        return (array)$this->client->issue->create($attributes);
    }
}