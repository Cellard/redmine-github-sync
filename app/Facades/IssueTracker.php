<?php


namespace App\Facades;


use App\IssueTracker\Contracts\IssueTrackerInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * Class Helpdesk
 * @package App\Facades
 *
 * @method static IssueTrackerInterface service($name, $key = null) Get project listing
 */
class IssueTracker extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'issueTracker';
    }
}
