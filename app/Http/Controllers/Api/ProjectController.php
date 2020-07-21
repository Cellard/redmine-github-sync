<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultResource;
use App\Project;

class ProjectController extends Controller
{
    public function milestones($id)
    {
        $milestones = Project::find($id)->milestones()->get();
        return DefaultResource::collection($milestones);
    }
}
