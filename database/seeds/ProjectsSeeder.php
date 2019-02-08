<?php

use App\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectsSeeder extends Seeder
{
    public function run()
    {
        array_map(function ($package) {
	        factory(Project::class)->create([
	        	'name' => Str::title($package),
	        	'vendor' => 'tightenco',
	        	'package' => $package,
	        ]);
    	}, [
    		'symposium',
			'gistlog',
			'giscus',
			'novapackages',
			'confomo',
			'laraveltricks-private',
			'fieldgoal',
			'sauce',
			'postit',
			'one-on-ones',
			'ozzie',
			'laracon-challenge',
			'laracon-visitors-guide',
    	]);
    }
}
