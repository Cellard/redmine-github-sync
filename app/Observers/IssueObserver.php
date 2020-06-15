<?php

namespace App\Observers;

use App\Issue;

class IssueObserver
{
    /**
     * Handle the issue "created" event.
     *
     * @param  \App\Issue  $issue
     * @return void
     */
    public function created(Issue $issue)
    {
        //
    }

    public function saving(Issue $issue)
    {
        $issue->open = ($status = $issue->status()) ? !@$status->more['is_closed'] : true;
    }

    /**
     * Handle the issue "deleted" event.
     *
     * @param  \App\Issue  $issue
     * @return void
     */
    public function deleted(Issue $issue)
    {
        //
    }

    /**
     * Handle the issue "restored" event.
     *
     * @param  \App\Issue  $issue
     * @return void
     */
    public function restored(Issue $issue)
    {
        //
    }

    /**
     * Handle the issue "force deleted" event.
     *
     * @param  \App\Issue  $issue
     * @return void
     */
    public function forceDeleted(Issue $issue)
    {
        //
    }
}
