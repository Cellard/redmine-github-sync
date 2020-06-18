<?php

namespace App\Observers;

use App\Issue;
use App\SyncedIssue;

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
        SyncedIssue::create([
            'issue_id' => $issue->id,
            'project_id' => $issue->project->id,
            'ext_id' => $issue->ext_id,
            'updated_at' => $issue->updated_at,
            'created_at' => $issue->created_at
        ]);
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
