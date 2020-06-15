<?php


namespace App\IssueTracker\Contracts;


use App\Project;
use App\Server;
use Illuminate\Support\Carbon;
use Jenssegers\Model\Model;

/**
 * Interface ProjectContract
 * @package App\IssueTracker\Contracts
 *
 * @mixin Model
 * @property-read integer $id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string|null $description
 * @property-read string $url
 * @property-read string $base_uri
 */
interface ProjectContract
{
    public function toArray();

    /**
     * Создает локальную копию записи
     * @param Server $server
     * @return Project
     */
    public function toLocal(Server $server);
}
