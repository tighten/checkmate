<?php

namespace App\Http\Controllers;

use App\Project;

class ProjectController extends Controller
{
    public function index()
    {
        return view('welcome', [
            'projects' => Project::valid()->active()->get()->sortBy('name'),
        ]);
    }
}
