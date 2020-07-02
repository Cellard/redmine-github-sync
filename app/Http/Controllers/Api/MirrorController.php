<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;
use App\Http\Resources\DefaultResource;
use App\Http\Resources\MirrorResource;
use App\Mirror;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class MirrorController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param ApiRequest $request
     * @return JsonResource
     */
    public function index(ApiRequest $request)
    {
        $mirrors = $request->user()->mirrors()->with(['left', 'left.server', 'right', 'right.server'])->get();
        return DefaultResource::collection($mirrors);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $mirror = Mirror::create([
            'user_id' => Auth::id(),
            'left_type' => 'App\Project',
            'left_id' => $request->left['project'],
            'right_type' => 'App\Project',
            'right_id' => $request->right['project'],
            'ltr_labels' => $request->ltrLabelsMap,
            'rtl_labels' => $request->rtlLabelsMap,
            'config' => $request->config
        ]);
        return new DefaultResource($mirror);
    }

    public function show(Mirror $mirror)
    {
        return MirrorResource::make($mirror);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $mirror = Mirror::find($id);
        $mirror->update([
            'user_id' => Auth::id(),
            'left_type' => 'App\Project',
            'left_id' => $request->left['project'],
            'right_type' => 'App\Project',
            'right_id' => $request->right['project'],
            'ltr_labels' => $request->ltrLabelsMap,
            'rtl_labels' => $request->rtlLabelsMap,
            'config' => $request->config
        ]);
        return new DefaultResource($mirror);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Mirror::find($id)->delete();
        return response()->json([
            'message' => 'Mirror deleted'
        ]);
    }
}
