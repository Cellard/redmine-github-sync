<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MirrorLabel extends Model
{
    protected $table = 'mirror_label';

    public $timestamps = false;
    
    protected $fillable = [
        'mirror_id', 'left_label_id', 'right_label_id'
    ];
}
