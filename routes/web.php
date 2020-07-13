<?php

Route::get('/', 'ProjectController@index')->name('project.index');
Route::get('ignored', 'IgnoredProjectController@index')->name('ignored.index');
Route::patch('ignore/{project}', 'IgnoreProjectController')->name('project.ignore');
Route::patch('unignore/{project}', 'UnIgnoreProjectController')->name('project.unignore');
