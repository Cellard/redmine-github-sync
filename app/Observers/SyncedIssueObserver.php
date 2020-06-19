<?php

namespace App\Observers;

use App\SyncedIssue;

class SyncedIssueObserver
{
    /**
     * Handle the synced issue "saved" event.
     *
     * @param  \App\SyncedIssue  $syncedIssue
     * @return void
     */
    public function saved(SyncedIssue $syncedIssue)
    {
        return;
        $parentIssue = $syncedIssue->parentIssue;
        if ($syncedIssue->updated_at->greaterThan($parentIssue->updated_at)) {
            $parentIssue->updated_at = $syncedIssue->updated_at;
            $parentIssue->save();
        }
    }
}
