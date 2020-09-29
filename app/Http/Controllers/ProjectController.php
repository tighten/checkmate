<?php

namespace App\Http\Controllers;

use App\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::valid()->active()->get()->sortBy(function ($project) {
            return $project->status;
        });

        return view('welcome', [
            'count' => $projects->count(),
            'projects' => $projects,
        ]);
    }
}
