<?php

namespace App\Http\Controllers;

use App\Project;

class ProjectController extends Controller
{
    public function index()
    {
        [$behind, $current] = Project::valid()->active()->get()->sortBy(function ($project) {
            return strtolower($project->name);
        })->partition(function ($project) {
            return $project->is_behind_latest;
        });

        return view('welcome', [
            'count' => $behind->count() + $current->count(),
            'behind' => $behind,
            'current' => $current,
        ]);
    }
}
