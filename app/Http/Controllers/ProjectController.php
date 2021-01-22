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
                return 1000000 + ($major * 10000) + ($minor * 100) + (int) $patch;
            }

            if ($project->status === Project::STATUS_BEHIND) {
                return 2000000 + ($major * 10000) + ($minor * 100) + (int) $patch;
            }

            return 3000000 + ($major * 100) + ($minor * 100) + (int) $patch;
        });

        if (auth()->guest() && ! (config('app.show_private_repos'))) {
            $projects = $projects->reject->is_private;
        }

        return view('welcome', [
            'projects' => $projects,
        ]);
    }
}
