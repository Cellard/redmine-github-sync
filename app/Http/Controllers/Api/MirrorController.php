<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\StoreServer;
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
        $mirrorLabels = [];
        $mirror = Mirror::create([
            'user_id' => Auth::id(),
            'left_type' => 'App\Project',
            'left_id' => $request->left['project'],
            'right_type' => 'App\Project',
            'right_id' => $request->right['project']
        ]);

        foreach ($request->labels as $label) {
            $mirrorLabels[] = [
                'left_label_id' => $label['left_label_id'],
                'right_label_id' => $label['right_label_id']
            ];
        }

        $mirror->labels()->createMany($mirrorLabels);
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
        $mirrorLabels = [];
        $mirror = Mirror::find($id);
        $mirror->update([
            'user_id' => Auth::id(),
            'left_type' => 'App\Project',
            'left_id' => $request->left['project'],
            'right_type' => 'App\Project',
            'right_id' => $request->right['project']
        ]);

        $mirror->labels()->delete();
        foreach ($request->labels as $label) {
            if (isset($label['left_label_id']) && isset($label['right_label_id'])) {
                $mirrorLabels[] = [
                    'left_label_id' => $label['left_label_id'],
                    'right_label_id' => $label['right_label_id']
                ];
            }
        }

        $mirror->labels()->createMany($mirrorLabels);
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
        //
    }
}
