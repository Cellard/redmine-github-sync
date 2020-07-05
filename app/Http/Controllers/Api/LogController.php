<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LogResource;
use App\Log;

class LogController extends Controller
{
    public function index()
    {
        return LogResource::collection(Log::orderBy('updated_at', 'desc')->take(20)->get());
    }
}
