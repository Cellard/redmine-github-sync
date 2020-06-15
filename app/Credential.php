<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ApiKey
 * @package App
 *
 * @property string $api_key
 * @property integer $ext_id
 * @property string $error
 * @property-read Server $server
 * @property-read User $user
 */
class Credential extends Pivot
{
    protected $table = 'credentials';
    public $incrementing = true;

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
