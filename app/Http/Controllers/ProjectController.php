<?php

namespace App\Http\Controllers;

use App\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::valid()->active()->get()->sortBy(function ($project) {
            [$major, $minor, $patch] = explode('.', $project->current_laravel_version);

            // 1st Insecure | 2nd Behind | 3rd Current
            if ($project->status === Project::STATUS_INSECURE) {
                return 10000 + ($major * 1000) + ($minor * 100) + (int) $patch;
            }

            if ($project->status === Project::STATUS_BEHIND) {
                return 20000 + ($major * 1000) + ($minor * 100) + (int) $patch;
            }

            return 30000 + ($major * 100) + ($minor * 100) + (int) $patch;
        });

        if (auth()->guest() && ! (config('app.show_private_repos'))) {
            $projects = $projects->reject->is_private;
        }

        return view('welcome', [
            'projects' => $projects,
        ]);
    }
}
