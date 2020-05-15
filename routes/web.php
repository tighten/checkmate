<?php

Route::get('/', 'ProjectController@index')->name('project.index');
Route::patch('ignore/{project}', 'IgnoreProjectController')->name('project.ignore');
Route::get('ignored', 'IgnoredProjectController@index')->name('ignored.index');
