<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Issue
 * @package App
 *
 * @property integer $id
 * @property integer $ext_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $started_at
 * @property Carbon $finished_at
 * @property string $subject
 * @property string $description
 * @property boolean $open
 * @property-read Project $project
 * @property-read null|Milestone $milestone
 * @property-read Collection|Label[] $enumerations
 * @property-read User $author
 * @property-read null|User $assignee
 */
class Issue extends Model
{
    protected $dates = ['started_at', 'finished_at'];
    protected $fillable = [
        'project_id', 'ext_id', 'author_id', 'subject', 'description', 'created_at', 'updated_at'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }
    public function enumerations()
    {
        return $this->belongsToMany(Label::class);
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * @return Collection|Label[]
     */
    public function labels()
    {
        return $this->enumerations()->whereNull('type')->get();
    }

    /**
     * @return null|Label
     */
    public function tracker()
    {
        return $this->enumerations()->where('type', 'tracker')->first();
    }

    /**
     * @return null|Label
     */
    public function status()
    {
        return $this->enumerations()->where('type', 'status')->first();
    }

    /**
     * @return null|Label
     */
    public function priority()
    {
        return $this->enumerations()->where('type', 'priority')->first();
    }

    public function syncedIssues()
    {
        return $this->hasMany(SyncedIssue::class, 'issue_id', 'id');
    }
}
