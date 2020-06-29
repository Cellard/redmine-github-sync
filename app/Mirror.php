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
        'config'
    ];

    protected $casts = [
        'ltr_labels' => 'array',
        'rtl_labels' => 'array'
    ];

    protected $dates = ['synced_at'];

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
}
