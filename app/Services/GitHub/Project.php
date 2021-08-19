<?php

namespace App\Services\GitHub;

use App\Project as EloquentProject;

class Project
{
    public $name;
    public $vendor;
    public $package;
    public $currentLaravelVersion;
    public $currentLaravelConstraint;
    public $isPrivate;


    public function __construct(array $repository)
    {
        $this->name = $repository['name'];
        $this->vendor = $repository['vendor'];
        $this->package = $repository['name'];
        $this->currentLaravelVersion = $repository['current_version'];
        $this->currentLaravelConstraint = $repository['constraint'];
        $this->isPrivate = $repository['is_private'];
    }

    public function sync()
    {
        $project = EloquentProject::firstOrCreate([
            'name' => $this->name,
            'vendor' => $this->vendor,
            'package' => $this->name,
        ], [
            'current_laravel_version' => $this->currentLaravelVersion,
            'current_laravel_constraint' => $this->currentLaravelConstraint,
            'is_valid' => true,
            'is_private' => $this->isPrivate,
        ]);

        if (! $this->versionDataHasChanged($project)) {
            return null;
        }

        $project->update([
            'current_laravel_version' => $this->currentLaravelVersion,
            'current_laravel_constraint' => $this->currentLaravelConstraint,
            'is_valid' => true,
            'is_private' => $this->isPrivate,
        ]);

        cache()->forget(sprintf(EloquentProject::DESIRED_VERSION_CACHE_KEY, $project->id));

        return "Updating {$project->name}'s version...";
    }

    private function versionDataHasChanged($project)
    {
        return $this->versionHasChanged($project, $this->currentLaravelVersion)
            || $this->constraintHasChanged($project, $this->currentLaravelConstraint);
    }

    private function versionHasChanged($project, $currentVersion)
    {
        return $project->current_laravel_version !== $currentVersion;
    }

    private function constraintHasChanged($project, $constaint)
    {
        return $project->current_laravel_constraint !== $constaint;
    }
}
