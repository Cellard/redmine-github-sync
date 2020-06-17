<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Label
 * @package App
 *
 * @property-read integer $id
 * @property string|null $type
 * @property string $name
 * @property-read  Server $server
 * @property integer $ext_id
 * @property array $more
 */
class Label extends Model
{
    use SoftDeletes;

    const TRACKER = 'tracker';
    const STATUS = 'status';
    const PRIORITY = 'priority';

    protected $fillable = ['server_id', 'ext_id', 'type', 'name'];
    protected $casts = [
        'more' => 'array'
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
