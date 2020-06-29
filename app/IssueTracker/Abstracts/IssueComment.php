<?php


namespace App\IssueTracker\Abstracts;

use Jenssegers\Model\Model;

/**
 * Class Issue
 * @package App\IssueTracker\Abstracts
 *
 */
class IssueComment extends Model
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'author' => $this->author->toArray(),
            'created_at' => $this->created_at,
        ];
    }
}
