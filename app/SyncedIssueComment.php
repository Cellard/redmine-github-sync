<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SyncedIssueComment extends Model
{
    protected $fillable = [
        'issue_comment_id',
        'project_id',
        'ext_id',
    ];
}
