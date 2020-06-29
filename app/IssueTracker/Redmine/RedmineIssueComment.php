<?php


namespace App\IssueTracker\Redmine;

use App\IssueTracker\Abstracts\IssueComment;
use Carbon\Carbon;

/**
 * Class Issue
 * @package App\IssueTracker\Abstracts
 *
 */
class RedmineIssueComment extends IssueComment
{
    public static function createFromRemote(array $attributes)
    {
        $attributes['body'] = $attributes['notes'];
        $attributes['author'] = RedmineUser::createFromRemote($attributes['user']);
        $attributes['created_at'] = Carbon::parse($attributes['created_on']);

        return new static($attributes);
    }
}
