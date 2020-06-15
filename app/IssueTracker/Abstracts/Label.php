<?php


namespace App\IssueTracker\Abstracts;


use App\IssueTracker\Contracts\LabelContract;
use App\IssueTracker\Contracts\ProjectContract;
use App\Project;
use Jenssegers\Model\Model;

class Label extends Model implements LabelContract
{
    public static function createFromRemote(array $attributes, ProjectContract $project)
    {
        return new static($attributes, $project);
    }

    protected $casts = [
        'more' => 'array'
    ];

    public function __construct(array $attributes, ProjectContract $project)
    {
        $attributes['project'] = $project;
        parent::__construct($attributes);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'more' => $this->more,
            'project' => $this->project->toArray()
        ];
    }

    /**
     * Создает локальную копию записи
     * @param Project $project
     * @param null|string $type
     * @return \App\Label
     */
    public function toLocal(Project $project, $type = null)
    {
        // Тут полиморфизм
        // У GitHub лейблы не типизированы, используй как хочешь, но зато они в одном пространстве идентификаторов.
        // Их можно сваливать в кучу без потери уникальности ключа.
        // А у Redmine есть три типа признаков, и каждый в своем пространстве идентификаторов.
        // Их уникальный ключ включает их тип, чтобы отличать одно от другого.

        $uniqueKey = ['project_id' => $project->id, 'ext_id' => $this->id];
        if ($type) {
            $uniqueKey['type'] = $type;
        }

        /** @var \App\Label $label */
        $label = \App\Label::withTrashed()->updateOrCreate(
            $uniqueKey,
            ['name' => $this->name]
        );
        $label->more = $this->more;
        $label->restore();

        return $label;
    }
}
