<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SyncedIssueFile extends Model
{
    protected $fillable = [
        'issue_file_id',
        'project_id',
        'ext_id',
    ];
}
