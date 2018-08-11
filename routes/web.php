<?php

use App\Project;

Route::get('/', function () {
    return view('welcome', [
        'projects' => Project::all(),
    ]);
});
