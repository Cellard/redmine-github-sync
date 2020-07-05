<?php

namespace App\Services\Downloaders;

use App\Label;
use App\Project;

class RedmineDownloader
{
    protected $client;
    protected $credential;

    public function __construct($credential)
    {
        $this->credential = $credential;
    }

    protected function connect(string $url, string $apiKey): void
    {
        $this->client = new \Redmine\Client($url, $apiKey);
    }

    public function download()
    {
        $this->connect($this->credential->server->base_uri, $this->credential->api_key);
        if (!$this->credential->ext_id) {
            $this->setCregentialExtId();
        }
        $this->downloadProjects();
        $this->downloadLabels();
    }

    private function setCregentialExtId()
    {
        $account = $this->client->user->getCurrentUser()['user'];
        $this->credential->ext_id = $account['id'];
        $this->credential->save();
    }

    private function downloadProjects()
    {
        $offset = 0;
        $totalCount = 1;
        $projects = [];
        while ($totalCount > count($projects)) {
            $response = $this->client->project->all(['offset' => $offset]);
            $offset += $response['limit'];
            $totalCount = $response['total_count'];
            $projects = array_merge($projects, $response['projects']);
        }
        foreach ($projects as $project) {
            Project::updateOrCreate(
                [
                    'server_id' => $this->credential->server->id,
                    'ext_id' => $project['id']
                ],
                [
                    'slug' => $project['identifier'],
                    'name' => $project['name'],
                    'description' => $project['description'] ?? null,
                ]
            );
        }
    }

    private function downloadLabels()
    {
        $this->downloadStatuses();
        $this->downloadTrackers();
        $this->downloadPriorities();
    }

    private function downloadTrackers()
    {
        $trackers = $this->client->tracker->all()['trackers'];
        foreach ($trackers as $tracker) {
            Label::updateOrCreate(
                [
                    'server_id' => $this->credential->server->id,
                    'ext_id' => $tracker['id'],
                    'type' => Label::TRACKER
                ],
                [
                    'type' => Label::TRACKER,
                    'name' => $tracker['name'],
                    'more' => $tracker['more'] ?? null
                ]
            );
        }
    }

    private function downloadStatuses()
    {
        $statuses = $this->client->issue_status->all()['issue_statuses'];
        foreach ($statuses as $status) {
            Label::updateOrCreate(
                [
                    'server_id' => $this->credential->server->id,
                    'ext_id' => $status['id'],
                    'type' => Label::STATUS
                ],
                [
                    'type' => Label::STATUS,
                    'name' => $status['name'],
                    'more' => $status['more'] ?? null
                ]
            );
        }
    }

    private function downloadPriorities()
    {
        $priorities = $this->client->issue_priority->all()['issue_priorities'];
        foreach ($priorities as $priority) {
            Label::updateOrCreate(
                [
                    'server_id' => $this->credential->server->id,
                    'ext_id' => $priority['id'],
                    'type' => Label::PRIORITY
                ],
                [
                    'type' => Label::PRIORITY,
                    'name' => $priority['name'],
                    'more' => $priority['more'] ?? null
                ]
            );
        }
    }
}