<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\StoreServer;
use App\Http\Resources\DefaultResource;
use App\Http\Resources\ServerResource;
use App\Jobs\Download;
use App\Server;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param ApiRequest $request
     * @return JsonResource
     */
    public function index()
    {
        return ServerResource::collection(Server::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServer $request)
    {
        $parsedUrl = parse_url($request->url);
        $server = Server::create([
            'name' => $request->name,
            'driver' => $request->driver,
            'base_uri' => $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/'
        ]);
        $credential = $server->credentials()->where('user_id', Auth::id())->first();
        if ($credential) {
            $credential->update([
                'api_key' => $request->api_key
            ]);
        } else {
            $credential = $server->credentials()->create([
                'user_id' => Auth::id(),
                'api_key' => $request->api_key
            ]);
        }
        Download::dispatch($credential);
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
        $parsedUrl = parse_url($request->url);
        $server->update([
            'driver' => $request->driver,
            'base_uri' => $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/'
        ]);
        $server->credentials()->where('user_id', Auth::id())->update([
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
        Server::find($id)->delete();
        return response()->json([
            'message' => 'Server deleted'
        ]);
    }

    public function projects($id)
    {
        return DefaultResource::collection(Server::find($id)->projects);
    }

    public function labels($id)
    {
        $labels = Server::find($id)->enumerations()->select(['type', 'id as value', 'name as label'])->orderBy('type')->get();
        $labels = $labels->groupBy('type')->map(function ($item, $key) {
            return [
                'label' => $key ? $key : 'label',
                'value' => $key ? $key : 'label',
                'children' => $item
            ];
        })->values();
        return DefaultResource::collection($labels);
    }
}
