<?php

namespace App\Services\Connectors;

class LocalRedmineConnector {

    private $client;

    public function __construct()
    {
        
    }
    public function pullIssues($project, $issuesFromUpdatedAtDate)
    {
        $issues = $this->client->getIssues($project, $issuesFromUpdatedAtDate);
        $this->saveIssues();
    }

    public function getIssues($project, $updatedDateTime)
    {        
        return $this->client->issue->all([
            'project_id' => $project->id,
            'status_id' => '*',
            'updated_on' => ">={$updatedDateTime->toIso8601ZuluString()}"
        ])['issues'];
    }
}