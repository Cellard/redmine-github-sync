<?php


namespace App\IssueTracker\Contracts;


use App\Label;
use App\Server;

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
     * @param Server $server
     * @return Label
     */
    public function toLocal(Server $server);
}
