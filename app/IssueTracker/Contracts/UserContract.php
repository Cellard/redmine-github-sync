<?php


namespace App\IssueTracker\Contracts;


use App\Server;
use App\User;
use Jenssegers\Model\Model;

/**
 * Interface UserContract
 * @package App\IssueTracker\Contracts
 *
 * @mixin Model
 * @property-read integer $id
 * @property-read string $email
 * @property-read string $name
 */
interface UserContract
{
    public function toArray();

    /**
     * Создает локальную копию пользователя на сервере
     * @param Server $server
     * @return User
     */
    public function toLocal(Server $server);
}
