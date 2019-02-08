<?php

use App\Project;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
    	$this->call([
    		ProjectsSeeder::class
    	]);
    }
}
