<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SyncedIssue extends Model
{
    protected $fillable = [
        'issue_id', 'project_id', 'ext_id', 'updated_at'
    ];

    public function parentIssue()
    {
        return $this->belongsTo(Issue::class, 'issue_id', 'id');
    }
}
