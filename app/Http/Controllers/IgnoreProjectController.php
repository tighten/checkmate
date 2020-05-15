<?php

namespace App\Http\Controllers;

use App\Project;

class IgnoreProjectController extends Controller
{
    public function __invoke(Project $project)
    {
        $project->update(['ignored' => true]);

        return redirect()->back();
    }
}
