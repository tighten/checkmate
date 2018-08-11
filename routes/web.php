<?php

use App\Project;

Route::get('/', function () {
    // dd(laravelActualFromComposerLock(composerLockForRepo('tightenco', 'symposium')));
    // dd(composerLockForRepo('tightenco', 'symposium'));
    // dd(laravelConstraintFromComposerJson(composerJsonForRepo('tightenco', 'symposium')));

    return view('welcome', [
        'projects' => Project::all(),
    ]);
});
