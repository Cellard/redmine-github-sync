<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Карта синхронизации всяческих объектов
 * @package App
 *
 * @property-read Model $left
 * @property-read Model $right
 * @property-read User $user создатель правила синхронизации
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Mirror onlyClass(string|array $classname)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Mirror for(Model $model)
 */
class Mirror extends Model
{
    protected $fillable = [
        'user_id',
        'left_type',
        'left_id',
        'right_type',
        'right_id',
        'ltr_labels',
        'rtl_labels',
        'synced_at',
        'start_date',
        'config'
    ];

    protected $casts = [
        'ltr_labels' => 'array',
        'rtl_labels' => 'array'
    ];

    protected $dates = ['synced_at', 'start_date'];

    public function left()
    {
        return $this->morphTo();
    }

    public function right()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function availableOwners()
    {
        $serverIds = Project::findMany($this->projects()->pluck('id')->toArray())->pluck('server_id')->toArray();
        return User::whereHas('credentials', function ($query) use ($serverIds) {
            $query->whereNotNull('api_key')->whereIn('server_id', $serverIds);
        })->get();
    }

    /**
     * @return \Illuminate\Support\Collection|Project[]
     */
    public function projects()
    {
        $left = $this->castToProject($this->left);
        $right = $this->castToProject($this->right);

        if ($left && $right) {
            return collect([$left, $right]);
        } elseif ($left) {
            return collect($left);
        } elseif ($right) {
            return collect($right);
        } else {
            return collect();
        }
    }

    /**
     * @param Model $morph
     * @return Project|Model|null
     */
    protected function castToProject(Model $morph)
    {
        if ($morph instanceof Milestone) {
            return $morph->project;
        } elseif ($morph instanceof Project) {
            return $morph;
        } else {
            return null;
        }
    }

    public function scopeOnlyClass(Builder $query, $classname)
    {
        $query
            ->whereIn('left_type', (array)$classname)
            ->whereIn('right_type', (array)$classname);
    }

    public function scopeFor(Builder $query, Model $model)
    {
        $query
            ->whereHasMorph('left', get_class($model),
                function (Builder $query) use ($model) {
                    $query->whereKey($model->getKey());
                })
            ->orWhereHasMorph('right', get_class($model),
                function (Builder $query) use ($model) {
                    $query->whereKey($model->getKey());
                });
    }

    public function labels()
    {
        return $this->hasMany(MirrorLabel::class);
    }

    public function getLabelsMap($project)
    {
        if ($project->id === $this->left->id) {
            return $this->ltr_labels;
        } else if ($project->id === $this->right->id) {
            return $this->ltr_labels;
        }
        throw new \Exception("Project is not included to mirror.");
    }

    public function getProjectPosition($project)
    {
        if ($project->id === $this->left->id) {
            return 'left';
        } else if ($project->id === $this->right->id) {
            return 'right';
        }
        throw new \Exception("Project is not included to mirror.");
    }

    public function queryIssuesToPush($position)
    {
        $project = $position === 'left' ? $this->left : $this->right;
        $mirrorProject = $position === 'left' ? $this->right : $this->left;

        if ($this->config === 'ltr' && $position === 'left'
        || $this->config === 'rtl' && $position === 'right') {
            return $project->queryIssuesToPush();
        }

        $mirrorIssues = Issue::whereHas('syncedIssues', function ($query) use ($mirrorProject) {
            $query->where('project_id', $mirrorProject->id);
        });

        return $mirrorIssues->whereHas('syncedIssues', function ($query) use ($project) {
            $query->where('project_id', $project->id)
                ->whereColumn('synced_issues.updated_at', '<', 'issues.updated_at');
        })->orWhereDoesntHave('syncedIssues', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        });
    }
}
