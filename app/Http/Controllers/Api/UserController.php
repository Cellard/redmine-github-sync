<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultResource;
use App\User;

class UserController extends Controller
{
    public function index()
    {
        return DefaultResource::collection(User::all());
    }
}
