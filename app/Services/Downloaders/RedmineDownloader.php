<?php

namespace App\Services\Downloaders;

use Str;
use App\Label;
use App\Project;
use App\User;
use Illuminate\Support\Facades\Log;

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
        try {
            $this->connect($this->credential->server->base_uri, $this->credential->api_key);
            $this->setCregentialExtId();
            $this->downloadProjects();
            $this->downloadLabels();
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }

    private function setCregentialExtId()
    {
        $account = $this->client->user->getCurrentUser()['user'];
        if (isset($account['mail'])) {
            $localUser = User::where('email', $account['mail'])->orWhere('name', $account['firstname'] . ' ' . ($account['lastname'] ?? ''))->first();
        } else {
            $localUser = User::where('name', $account['firstname'] . ' ' . ($account['lastname'] ?? ''))->first();
        }

        if (!$localUser) {
            $localUser = User::create([
                'email' => $account['mail'] ?? null,
                'name' => $account['firstname'] . ' ' . ($account['lastname'] ?? ''),
                'password' => Str::random(64)
            ]);
        } else if (isset($account['mail'])) {
            $localUser->update([
                'email' => $account['mail']
            ]);
        }
        $this->credential->ext_id = $account['id'];
        $this->credential->user_id = $localUser->id;
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
            if ($project['name'] === 'ФК «Зенит»') {
                $test = '123';
            }
            if (isset($project['parent'])) {
                $parent = Project::where([
                    'server_id' => $this->credential->server->id,
                    'ext_id' => $project['parent']['id']
                ])->first();
                $parentId = $parent ? $parent->id : null; 
            } else {
                $parentId = null;
            }

            Project::updateOrCreate(
                [
                    'server_id' => $this->credential->server->id,
                    'ext_id' => $project['id']
                ],
                [
                    'parent_id' => $parentId,
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