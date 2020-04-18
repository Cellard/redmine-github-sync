<?php

namespace App;

use App\Exceptions\DriverNotSupportedException;
use App\IssueTracker\Contracts\IssueTrackerInterface;
use App\IssueTracker\Gogs\GogsIssueTracker;
use App\IssueTracker\Redmine\RedmineIssueTracker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Server
 * @package App
 *
 * @property string $name
 * @property string $driver
 * @property string $base_uri
 */
class Server extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'name';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * @param $name
     * @return static
     */
    public static function find($name)
    {
        /** @var static $server */
        $server = static::query()->whereKey($name)->firstOrFail();

        return $server;
    }

    /**
     * @param User|string $credentials
     * @return IssueTrackerInterface
     * @throws DriverNotSupportedException
     */
    public function connect($credentials) {

        if ($credentials instanceof User) {
            $api_key = $this->users()->whereKey($credentials->getKey())->firstOrFail()->pivot->api_key;
        } else {
            $api_key = $credentials;
        }

        switch ($this['driver']) {
            case 'redmine':
                return new RedmineIssueTracker($this->base_uri, $api_key);
            case 'gogs':
                return new GogsIssueTracker($this->base_uri, $api_key);
            default:
                throw new DriverNotSupportedException("Driver [{$this['driver']}] is not supported.");
        }
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class, (new ApiKey())->getTable(),
            'server', 'user_id')
            ->using(ApiKey::class)
            ->withPivot('api_key')
            ->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'server', 'name');
    }
}
