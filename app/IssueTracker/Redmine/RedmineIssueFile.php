<?php


namespace App\IssueTracker\Redmine;

use App\IssueTracker\Abstracts\IssueFile;
use Carbon\Carbon;

/**
 * Class Issue
 * @package App\IssueTracker\Abstracts
 *
 */
class RedmineIssueFile extends IssueFile
{
    public static function createFromRemote(array $attributes)
    {
        $attributes['name'] = $attributes['filename'];
        $attributes['author'] = RedmineUser::createFromRemote($attributes['author']);
        $attributes['created_at'] = Carbon::parse($attributes['created_on']);

        return new static($attributes);
    }
}
