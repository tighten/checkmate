<?php

namespace App\Http\Controllers;

use App\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::valid()->active()->get()->sortBy(function ($project) {
            // 1st Insecure | 2nd Behind | 3rd Current
            if ($project->status === Project::STATUS_INSECURE) {
                return 1;
            }
            if ($project->status === Project::STATUS_BEHIND) {
                return 2;
            }
            return 3;
        });

        if (auth()->guest()) {
            $projects = $projects->reject->is_private;
        }

        return view('welcome', [
            'projects' => $projects,
        ]);
    }
}
