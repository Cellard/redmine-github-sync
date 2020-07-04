<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Server;
use Illuminate\Support\Str;

class DriverController extends Controller
{
    public function index()
    {
        $data = [];

        foreach (Server::DRIVERS as $driver) {
            $data[] = ['id' => $driver, 'name' => Str::title($driver)];
        }

        return ['data' => $data];
    }
}
