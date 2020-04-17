<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tracker
 * @package App
 *
 * @property string $api_key
 * @property string $tracker
 */
class Tracker extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
