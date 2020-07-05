<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogError extends Model
{
    protected $fillable = [
        'message',
        'log_id'
    ];

    public function log()
    {
        return $this->belongsTo(Log::class);
    }
}
