<?php

namespace App\Http\Controllers;

use App\Project;

class UnIgnoreProjectController extends Controller
{
    public function __invoke(Project $project)
    {
        $project->update(['ignored' => false]);
        return response()->json([], 200);
    }
}
