<?php

namespace App\Http\Controllers;

use App\Project;

class IgnoredProjectController extends Controller
{
    public function index()
    {
        $projects = Project::ignored()->get()->sortBy(function ($project) {
            return strtolower($project->name);
        });

        if (auth()->guest() && ! (config('app.show_private_repos'))) {
            $projects = $projects->reject->is_private;
        }

        return view('ignored', [
            'projects' => $projects,
        ]);
    }
}
