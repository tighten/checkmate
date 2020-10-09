<?php

namespace App\Http\Controllers;

use App\Project;

class UnIgnoreProjectController extends Controller
{
    public function __invoke(Project $project)
    {
        $project->update(['ignored' => false]);

				if (request()->wantsJson()) {
					return response()->json([
						'success' => true
					], 200);
				}

        return redirect()->back();
    }
}
