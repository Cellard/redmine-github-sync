<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Хранит связи синхронизированных комментариев задач по проектам
 * issue_id - ссылка на локальную задчу в таблице issues
 * project_id - ссылка на проект, к которому относится синхронизированный комментарий
 * ext_id - внешний ИД комментария в Redmine
 */
class SyncedIssueComment extends Model
{
    protected $fillable = [
        'issue_comment_id',
        'project_id',
        'ext_id',
    ];
}
