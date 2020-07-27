<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Хранит связи синхронизированных задач по проектам
 * issue_id - ссылка на локальную задчу в таблице issues
 * project_id - ссылка на проект, к которому относится синхронизированная задача
 * ext_id - внешний ИД задачи в Redmine
 */
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
