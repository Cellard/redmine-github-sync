<?php


namespace App\IssueTracker\Redmine;


use App\IssueTracker\Abstracts\User;

class RedmineUser extends User
{
    public static function createFromRemote(array $attributes)
    {
        if (isset($attributes['login'])) {
            $attributes['name'] = $attributes['login'];
        } else if (isset($attributes['firstname'])) {
            $attributes['name'] = $attributes['firstname'];
        } else if (isset($attributes['name'])) {
            $attributes['name'] = $attributes['name'];
        } else {
            $attributes['name'] = 'unknown';
        }


        if (isset($attributes['mail']))
            $attributes['email'] = $attributes['mail'];

        return new static($attributes);
    }
}
