<?php


namespace App\IssueTracker\Contracts;


use App\Label;
use App\Project;
use Jenssegers\Model\Model;

/**
 * Interface LabelContract
 * @package App\IssueTracker\Contracts
 *
 * @mixin Model
 * @property-read integer $id
 * @property-read string $name
 * @property-read array $more
 * @property-read ProjectContract $project
 */
interface LabelContract
{
    public function toArray();

    /**
     * Создает локальную копию записи
     * @param Project $project
     * @return Label
     */
    public function toLocal(Project $project);
}
