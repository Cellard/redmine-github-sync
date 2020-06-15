<?php


namespace App\IssueTracker\Gogs;


use App\IssueTracker\Abstracts\Label;
use App\IssueTracker\Abstracts\IssueTracker;
use App\IssueTracker\AccessException;
use App\IssueTracker\Contracts\IssueContract;
use App\IssueTracker\Contracts\LabelContract;
use App\IssueTracker\Contracts\MilestoneContract;
use App\IssueTracker\Contracts\ProjectContract;
use App\IssueTracker\Contracts\UserContract;
use App\IssueTracker\Contracts\WithLabels;
use bconnect\GogsClient\GogsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;

class GogsIssueTracker extends IssueTracker implements WithLabels
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

    /**
     * @param int $page
     * @return ProjectContract[]|Collection
     *
     * @see https://github.com/gogs/docs-api/tree/master/Repositories
     */
    public function getProjects($page = 1)
    {
        $json = $this->requestProjects($page);

        $projects = [];

        foreach ($json as $value) {
            $projects[] = GogsProject::createFromRemote($value, $this->getBaseUri());
        }

        return collect($projects);
    }

    /**
     * @param ProjectContract $project
     * @return MilestoneContract[]|Collection
     *
     * @see https://github.com/gogs/docs-api/blob/master/Issues/Milestones.md
     */
    public function getMilestones(ProjectContract $project)
    {
        $response = $this->client()->get("/api/v1/repos/{$project->slug}/milestones");
        $json = json_decode($response->getBody(), true);

        $milestones = [];

        foreach ($json as $value) {
            $milestones[] = GogsMilestone::createFromRemote($value, $project);
        }

        return collect($milestones);
    }

    /**
     * Get project milestone listing
     * @param ProjectContract $project
     * @return Collection|LabelContract[]
     * @see https://github.com/gogs/docs-api/blob/master/Issues/Labels.md
     */
    public function getLabelListing(ProjectContract $project)
    {
        $response = $this->client()->get("/api/v1/repos/{$project->slug}/labels");
        $json = json_decode($response->getBody(), true);

        $labels = [];

        foreach ($json as $value) {
            $labels[] = Label::createFromRemote($value, $project);
        }

        return collect($labels);
    }

    /**
     * @param ProjectContract $project
     * @return Collection|IssueContract[]
     * @see https://github.com/gogs/docs-api/tree/master/Issues#list-issues-for-a-repository
     */
    public function getIssues(ProjectContract $project)
    {
        $response = $this->client()->get("/api/v1/repos/{$project->slug}/issues");
        $json = json_decode($response->getBody(), true);

        $issues = [];

        foreach ($json as $value) {
            $issues[] = GogsIssue::createFromRemote($value, $project);
        }

        return collect($issues);
    }

    /**
     * Get current user
     * @return null|UserContract
     * @see https://github.com/gogs/docs-api/tree/master/Users#get-the-authenticated-user
     * @throws AccessException
     */
    public function getAccount()
    {
        try {
            $response = $this->client()->get("/api/v1/user");
            $json = json_decode($response->getBody(), true);
            return GogsUser::createFromRemote($json);
        } catch (\Exception $e) {
            throw new AccessException($e->getMessage(), $e->getCode());
        }
    }
}
