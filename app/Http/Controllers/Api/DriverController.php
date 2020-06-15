<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;
use App\Http\Resources\DefaultResource;
use App\Server;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class DriverController extends Controller
{
    public function index(ApiRequest $request)
    {
        $data = [];

        foreach (Server::$drivers as $driver) {
            $data[] = ['id' => $driver, 'name' => Str::title($driver)];
        }

        return ['data' => $data];
    }
}
