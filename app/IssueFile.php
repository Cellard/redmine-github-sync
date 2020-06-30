<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IssueFile extends Model
{
    protected $fillable = [
        'name',
        'description',
        'path',
        'ext_id',
        'author_id',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function syncedFiles()
    {
        return $this->hasMany(SyncedIssueFile::class, 'issue_file_id', 'id');
    }
}
