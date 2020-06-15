<?php

namespace App\Observers;

use App\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function saving(User $user)
    {
        if (!$user->api_token) {
            $user->api_token = Str::random(80);
        }
    }
}
