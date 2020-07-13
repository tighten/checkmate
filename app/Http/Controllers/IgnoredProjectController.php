<?php

namespace App\Http\Controllers;

use App\Project;

class IgnoredProjectController extends Controller
{
    public function index()
    {
        return view('ignored', [
            'projects' => Project::ignored()->get()->sortBy(function ($project) {
                return strtolower($project->name);
            }),
        ]);
    }
}
