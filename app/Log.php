<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'status',
        'type',
        'mirror_id'
    ];

    public function mirror()
    {
        return $this->belongsTo(Mirror::class);
    }

    public function errors()
    {
        return $this->hasMany(LogError::class);
    }
}
