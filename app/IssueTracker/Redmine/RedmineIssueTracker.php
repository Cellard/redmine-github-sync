<?php


namespace App\IssueTracker\Redmine;

use App\IssueTracker\Abstracts\IssueTracker;
use App\IssueTracker\Contracts\MilestoneContract;
use App\IssueTracker\Contracts\ProjectContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;

class RedmineIssueTracker extends IssueTracker
{

    protected function client()
    {
        return new Client([
            'base_uri' => $this->getBaseUri(),
            'headers' => [
                'X-Redmine-API-Key' => $this->getApiKey()
            ]
        ]);
    }

    /**
     * Request projects
     * @param int $limit
     * @param int $offset
     * @return array
     */
    protected function requestProjects($limit = 25, $offset = 0)
    {
        $response = $this->client()->get('/projects.json', [
            'query' => [
                'limit' => $limit,
                'offset' => $offset
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getProjects($page = 1)
    {
        $limit = 100;
        $offset = $this->getOffset($page, $limit);

        $json = $this->requestProjects($limit, $offset);

        $projects = [];

        foreach ($json['projects'] as $value) {
            $projects[] = new RedmineProject($value, $this->getBaseUri());
        }

        return collect($projects);
    }

    public function getMilestones($project)
    {
        $response = $this->client()->get("/projects/{$project->identifier}/versions.json");
        $json = json_decode($response->getBody(), true);

        $milestones = [];

        foreach ($json['versions'] as $value) {
            if ($value['status'] == 'open') {
                $milestones[] = new RedmineMilestone($value, $project);
            }
        }

        return collect($milestones);
    }
}
