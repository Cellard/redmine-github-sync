<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Хранит связи синхронизированных файлов задач по проектам
 * issue_id - ссылка на локальную задчу в таблице issues
 * project_id - ссылка на проект, к которому относится синхронизированный файл
 * ext_id - внешний ИД файла в Redmine
 */
class SyncedIssueFile extends Model
{
    protected $fillable = [
        'issue_file_id',
        'project_id',
        'ext_id',
    ];
}
