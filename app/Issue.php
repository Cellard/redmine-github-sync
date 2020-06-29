<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    public function comments()
    {
        return $this->hasMany(IssueComment::class);
    }

    /**
     * Return eloquient builder of issues linked to remote issues by 'ext_id' field
     *
     * @param integer $remoteIssueId
     * @param integer $projectId
     * @return Builder
     */
    public function queryByRemote(int $remoteIssueId, int $projectId): Builder
    {
        return $this->where([
            'ext_id' => $remoteIssueId,
            'project_id' => $projectId
        ])->orWhereHas('syncedIssues', function ($query) use ($remoteIssueId, $projectId) {
            $query->where([
                'ext_id' => $remoteIssueId,
                'project_id' => $projectId
            ]);
        });
    }

    public function commentsToPush(int $projectId)
    {
        return $this->comments()->whereDoesntHave('syncedComments', function ($query) use ($projectId) {
            $query->where('project_id', $projectId);
        });
    }
}
