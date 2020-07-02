<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IssueComment extends Model
{
    protected $fillable = [
        'body',
        'ext_id',
        'author_id',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function syncedComments()
    {
        return $this->hasMany(SyncedIssueComment::class, 'issue_comment_id', 'id');
    }

    public function queryByExternalId($extId)
    {
        return $this->where('ext_id', $extId)->orWhereHas('syncedComments', function($query) use ($extId) {
            $query->where('ext_id', $extId);
        });
    }
}
