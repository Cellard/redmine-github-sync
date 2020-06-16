<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Milestone
 * @package App
 *
 * @property integer $id
 * @property-read Project $project
 * @property integer $ext_id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $due_on
 * @property Collection|Label[] $enumerations
 */
class Milestone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'ext_id',
        'name', 'description', 'due_on'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function server()
    {
        return $this->project->server();
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function enumerations()
    {
        return $this->project->enumerations();
    }

    public function getEnumerationsAttribute()
    {
        return $this->project->enumerations;
    }
}
