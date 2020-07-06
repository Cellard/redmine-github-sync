<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property string $api_token
 * @property-read Collection|Server[] $servers
 * @property-read Collection|Project[] $projects
 * @property-read Collection|Milestone[] $milestones
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function servers()
    {
        return $this->belongsToMany(Server::class, (new Credential())->getTable())
            ->using(Credential::class)
            ->as('credential')
            ->withPivot('api_key')
            ->withTimestamps();
    }

    public function credentials()
    {
        return $this->hasMany(Credential::class);
    }

    public function mirrors()
    {
        return $this->hasMany(Mirror::class);
    }

    /**
     * @param null|Server $server
     * @return BelongsToMany
     */
    public function projects($server = null)
    {
        $projects = $this->belongsToMany(Project::class)->withTimestamps();

        if ($server) {

            if ($server instanceof Server)
                $projects->where('server_id', $server->id);

        }

        return $projects;
    }

    /**
     * @param null|Server|Project $scope
     * @return BelongsToMany
     */
    public function milestones($scope = null)
    {
        $milestones = $this->belongsToMany(Milestone::class)->withTimestamps();

        if ($scope) {

            if ($scope instanceof Project)
                $milestones->where('project_id', $scope->id);

            if ($scope instanceof Server)
                $milestones->whereHas('project', function (Builder $query) use ($scope) {
                    $query->where('servers_id', $scope->id);
                });

        }

        return $milestones;
    }

}
