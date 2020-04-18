<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ApiKey
 * @package App
 *
 * @property string $api_key
 */
class ApiKey extends Pivot
{
    //

    public function server()
    {
        return $this->belongsTo(Server::class, 'server', 'name');
    }
}
