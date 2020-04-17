<?php


namespace App\IssueTracker\Abstracts;


use App\IssueTracker\Contracts\ProjectContract;
use Jenssegers\Model\Model;

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
            'identifier' => $this->identifier,
            'description' => $this->description,
            'created_at' => (string)$this->created_at,
            'url' => $this->url
        ];
    }
}
