<?php


namespace App\IssueTracker\Abstracts;


use App\Credential;
use App\IssueTracker\Contracts\UserContract;
use App\Server;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Jenssegers\Model\Model;

abstract class User extends Model implements UserContract
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name
        ];
    }

    /**
     * @param Server $server
     * @return \App\User|null
     */
    public function toLocal(Server $server)
    {
        /** @var Credential $credential */
        if ($credential = $server->credentials()
            ->where('ext_id', $this->id)
            ->first()) {
        } else {
            $user = \App\User::query()->firstOrCreate([
                'email' => $this->email
            ], [
                'name' => $this->name,
                'email_verified_at' => Carbon::now(),
                'password' => Str::random(64)
            ]);

            $credential = new Credential();
            $credential->user()->associate($user);
            $credential->server()->associate($server);
            $credential->ext_id = $this->id;
            $credential->save();
        }
        return $credential->user;
    }
}
