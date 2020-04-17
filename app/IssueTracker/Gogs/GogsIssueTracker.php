<?php


namespace App\IssueTracker\Gogs;


use App\IssueTracker\Abstracts\IssueTracker;
use App\IssueTracker\Contracts\MilestoneContract;
use App\IssueTracker\Contracts\ProjectContract;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class GogsIssueTracker extends IssueTracker
{

    protected function client()
    {
        return new Client([
            'base_uri' => $this->getBaseUri(),
            'headers' => [
                'Authorization' => "token {$this->getApiKey()}"
            ]
        ]);
    }

    /**
     * Request projects
     * @param int $page
     * @return array
     */
    protected function requestProjects($page = 1)
    {
        $response = $this->client()->get('/api/v1/user/repos', [
            'query' => [
                'page' => $page
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getProjects($page = 1)
    {
        $json = $this->requestProjects($page);

        $projects = [];

        foreach ($json as $value) {
            $projects[] = new GogsProject($value, $this->getBaseUri());
        }

        return collect($projects);
    }

    public function getMilestones($project)
    {
        $response = $this->client()->get("/api/v1/repos/{$project->identifier}/milestones");
        $json = json_decode($response->getBody(), true);

        $milestones = [];

        foreach ($json as $value) {
            $milestones[] = new GogsMilestone($value, $project);
        }

        return collect($milestones);
    }
}
