<?php

namespace App\Actions;

use App\Project;

class SyncProject
{
    public function __invoke(array $repository): ?string
    {
        $project = Project::firstOrCreate([
            'name' => $repository['name'],
            'vendor' => $repository['vendor'],
            'package' => $repository['name'],
        ], [
            'current_laravel_version' => $repository['current_version'],
            'current_laravel_constraint' => $repository['constraint'],
            'is_valid' => true,
            'is_private' => $repository['is_private'],
        ]);

        if (! $this->versionDataHasChanged($project, $repository)) {
            return null;
        }

        $project->update([
            'current_laravel_version' => $repository['current_version'],
            'current_laravel_constraint' => $repository['constraint'],
            'is_valid' => true,
            'is_private' => $repository['is_private'],
        ]);

        cache()->forget(sprintf(Project::DESIRED_VERSION_CACHE_KEY, $project->id));

        return "Updating {$project->name}'s version...";
    }

    private function versionDataHasChanged($project, $repository)
    {
        return $this->versionHasChanged($project, $repository['current_version'])
            || $this->constraintHasChanged($project, $repository['constraint']);
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
