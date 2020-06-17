<?php

namespace App;

use App\IssueTracker\Contracts\ProjectContract;
use App\IssueTracker\Gogs\GogsProject;
use App\IssueTracker\Redmine\RedmineProject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Project
 * @package App
 *
 * @property integer $id
 * @property Server $server
 * @property integer $ext_id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property-read Label[]|Collection $enumerations
 * @property-read Label[]|Collection $labels
 * @property-read Label[]|Collection $trackers
 * @property-read Label[]|Collection $statuses
 * @property-read Label[]|Collection $priorities
 */
class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'server_id', 'ext_id', 'slug', 'name', 'description'
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function syncedIssues()
    {
        return $this->hasMany(SyncedIssue::class);
    }

    public function enumerations()
    {
        return $this->server->hasMany(Label::class);
    }

    public function labels()
    {
        return $this->enumerations()->whereNull('type');
    }

    public function trackers()
    {
        return $this->enumerations()->where('type', 'tracker');
    }

    public function statuses()
    {
        return $this->enumerations()->where('type', 'status');
    }

    public function priorities()
    {
        return $this->enumerations()->where('type', 'priority');
    }

    /**
     * @return ProjectContract
     */
    public function contract()
    {
        switch ($this->server->driver) {
            case 'gogs':
                return GogsProject::createFromLocal($this);
            case 'redmine':
                return RedmineProject::createFromLocal($this);
        }
    }
}
