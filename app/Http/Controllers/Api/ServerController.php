<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\StoreServer;
use App\Http\Resources\DefaultResource;
use App\Http\Resources\ServerResource;
use App\Server;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param ApiRequest $request
     * @return JsonResource
     */
    public function index(ApiRequest $request)
    {
        return DefaultResource::collection($request->user()->servers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServer $request)
    {
        $server = Server::create([
            'id' => parse_url($request->url)['host'],
            'driver' => $request->driver,
            'base_uri' => rtrim($request->url, '/')
        ]);
        $server->credentials()->create([
            'user_id' => Auth::id(),
            'api_key' => $request->api_key
        ]);
        return new ServerResource($server);
    }

    public function show(Server $server)
    {
        return ServerResource::make($server);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreServer $request, $id)
    {
        $server = Server::find($id);
        $server->update([
            'driver' => $request->driver,
            'base_uri' => rtrim($request->url, '/')
        ]);
        $server->credentials()->update([
            'user_id' => Auth::id(),
            'api_key' => $request->api_key,
            'ext_id' => null,
            'username' => null
        ]);
        return new ServerResource($server);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
