<?php


namespace App\IssueTracker\Abstracts;


use App\IssueTracker\Contracts\ProjectContract;
use App\Server;
use Jenssegers\Model\Model;

/**
 * Class Project
 * @package App\IssueTracker\Abstracts
 *
 * @property-read string $base_uri
 */
abstract class Project extends Model implements ProjectContract
{
    public function __construct(array $attributes, string $base_uri)
    {
        $attributes['base_uri'] = $base_uri;
        parent::__construct($attributes);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'url' => $this->url
        ];
    }

    public function toLocal(Server $server)
    {
        /** @var \App\Project $project */
        $project = \App\Project::withTrashed()->updateOrCreate(
            [
                'server_id' => $server->id,
                'ext_id' => $this->id
            ],
            [
                'slug' => $this->slug,
                'name' => $this->name,
                'description' => $this->description
            ]
        );
        $project->restore();
        return $project;
    }
}
