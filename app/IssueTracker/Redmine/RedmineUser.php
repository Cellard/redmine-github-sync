<?php


namespace App\IssueTracker\Redmine;


use App\IssueTracker\Abstracts\User;

class RedmineUser extends User
{
    public static function createFromRemote(array $attributes)
    {
        if (isset($attributes['login']))
            $attributes['name'] = $attributes['login'];

        if (isset($attributes['mail']))
            $attributes['email'] = $attributes['mail'];

        return new static($attributes);
    }
}
