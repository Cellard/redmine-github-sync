<?php

namespace App\Observers;

use App\Issue;

class IssueObserver
{

    public function saving(Issue $issue)
    {
        if (count($issue->getDirty()) === 1 && isset($issue->getDirty()['updated_at'])) {
            return false;
        }
        $issue->open = ($status = $issue->status()) ? !@$status->more['is_closed'] : true;
    }
}
