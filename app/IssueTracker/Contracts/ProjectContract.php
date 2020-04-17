<?php


namespace App\IssueTracker\Contracts;


use Illuminate\Support\Carbon;
use Jenssegers\Model\Model;

/**
 * Interface ProjectContract
 * @package App\IssueTracker\Contracts
 *
 * @mixin Model
 * @property-read integer $id
 * @property-read string $name
 * @property-read string $identifier
 * @property-read string $description
 * @property-read Carbon $created_at
 * @property-read string $url
 */
interface ProjectContract
{

}
