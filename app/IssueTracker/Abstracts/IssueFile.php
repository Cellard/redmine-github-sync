<?php


namespace App\IssueTracker\Abstracts;

use Jenssegers\Model\Model;

/**
 * Class Issue
 * @package App\IssueTracker\Abstracts
 *
 */
class IssueFile extends Model
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'description' => $this->description,
            'author' => $this->author->toArray(),
            'created_at' => $this->created_at,
        ];
    }
}
