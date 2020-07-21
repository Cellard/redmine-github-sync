<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMirror;
use App\Http\Resources\DefaultResource;
use App\Http\Resources\MirrorResource;
use App\Mirror;
use App\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MirrorController extends Controller
{
    public function index()
    {
        $mirrors = Mirror::with(['left', 'left.server', 'right', 'right.server'])->get();
        return DefaultResource::collection($mirrors);
    }

    public function store(StoreMirror $request)
    {
        $mirror = Mirror::create([
            'user_id' => Auth::id(),
            'left_type' => Project::class,
            'left_id' => $request->left['project'],
            'right_type' => Project::class,
            'right_id' => $request->right['project'],
            'ltr_labels' => $request->ltrLabelsMap,
            'rtl_labels' => $request->rtlLabelsMap,
            'config' => $request->config,
            'start_date' => Carbon::parse($request->startDate)->setTimezone(config('app.timezone')),
            'owner_id' => $request->owner
        ]);
        return new DefaultResource($mirror);
    }

    public function show(Mirror $mirror)
    {
        return MirrorResource::make($mirror);
    }

    public function update(StoreMirror $request, $id)
    {
        $mirror = Mirror::find($id);
        $mirror->update([
            'user_id' => Auth::id(),
            'left_type' => 'App\Project',
            'left_id' => $request->left['project'],
            'left_milestone_id' => $request->left['milestone'] ?? null,
            'right_type' => 'App\Project',
            'right_id' => $request->right['project'],
            'right_milestone_id' => $request->right['milestone'] ?? null,
            'ltr_labels' => $request->ltrLabelsMap,
            'rtl_labels' => $request->rtlLabelsMap,
            'config' => $request->config,
            'start_date' => Carbon::parse($request->startDate)->setTimezone(config('app.timezone')),
            'owner_id' => $request->owner
        ]);
        return new DefaultResource($mirror);
    }

    public function destroy($id)
    {
        Mirror::find($id)->delete();
        return response()->json([
            'message' => 'Mirror deleted'
        ]);
    }
}
