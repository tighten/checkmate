<?php

namespace App\Http\Controllers;

use App\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $statusSorts = [Project::STATUS_INSECURE => -1, Project::STATUS_BEHIND => 0, Project::STATUS_CURRENT => 1];

        $projects = Project::valid()->active()->get()->sortBy([
            fn($a, $b) => $statusSorts[$a->status] <=> $statusSorts[$b->status],
            fn($a, $b) => $a->current_laravel_version <=> $b->current_laravel_version,
        ]);

        if (auth()->guest() && ! (config('app.show_private_repos'))) {
            $projects = $projects->reject->is_private;
        }

        return view('welcome', [
            'projects' => $projects,
        ]);
    }
}
