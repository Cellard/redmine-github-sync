<?php

namespace App;

use App\Exceptions\DriverNotSupportedException;
use App\IssueTracker\Contracts\IssueTrackerInterface;
use App\IssueTracker\Gogs\GogsIssueTracker;
use App\IssueTracker\Redmine\RedmineIssueTracker;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Server
 * @package App
 *
 * @property string $id
 * @property string $driver
 * @property string $base_uri
 * @property-read Collection|Project[] $projects
 * @property-read Collection|User[] $users
 * @property-read Collection|Credential[] $credentials
 */
class Server extends Model
{
    use SoftDeletes;

    public static $drivers = [
        'gogs', 'redmine'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'driver', 'base_uri'];

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

    public function getSlugAttribute()
    {
        return $this->id;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, (new Credential())->getTable())
            ->using(Credential::class)
            ->withPivot('api_key')
            ->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @param null|Model|Authenticatable $user
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function credentials($user = null)
    {
        $relation = $this->hasMany(Credential::class);

        if ($user) {
            $relation->where('user_id', $user->getKey());
        }

        return $relation;
    }
}
