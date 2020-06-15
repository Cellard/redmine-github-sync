<?php


namespace App\IssueTracker\Gogs;


use App\IssueTracker\Abstracts\User;

class GogsUser extends User
{
    public static function createFromRemote(array $attributes)
    {
        $attributes['name'] = $attributes['username'];
        return new static($attributes);
    }
}
