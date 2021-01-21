<?php

Route::get('/', 'ProjectController@index')->name('projects.index');
Route::get('ignored', 'IgnoredProjectController@index')->name('ignored.index');
Route::patch('ignore/{project}', 'IgnoreProjectController')->name('projects.ignore');
Route::patch('unignore/{project}', 'UnIgnoreProjectController')->name('projects.unignore');
