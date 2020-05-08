<?php

namespace App;

use Github\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'ignored' => 'boolean',
    ];

    public function getLaravelConstraintAttribute()
    {
        return app(GitInfoParser::class)->laravelConstraint($this->vendor, $this->package);
    }

    public function getLaravelVersionAttribute()
    {
        // @todo: go away from this method...have a job sync these periodically and just pull from db?
        return app(GitInfoParser::class)->laravelVersion($this->vendor, $this->package);
    }

    public function getDesiredLaravelVersionAttribute()
    {
        [$major, $minor] = explode('.', $this->current_laravel_version);

        $version = LaravelVersion::where([
            'major' => $major,
            'minor' => $minor,
        ])->firstOrFail();

        return (string) $version;
    }

    public function getGithubUrlAttribute()
    {
        return 'https://github.com/' . $this->vendor . '/' . $this->package;
    }

    public function getIsBehindLatestAttribute()
    {
        return version_compare($this->desired_laravel_version, $this->laravel_version) > 0;
    }

    public function presentStatus()
    {
        if ($this->is_behind_latest) {
            return '<span style="font-weight: bold; color: red;">BEHIND</span>';
        }

        return '<span style="font-weight: bold; color: green;">CURRENT</span>';
    }

    public function scopeActive($query)
    {
        return $query->where('ignored', 0);
    }

    public function syncLaravelVersionAndConstraint()
    {
        try {
            $currentVersion = app(GitInfoParser::class)->laravelVersion($this->vendor, $this->package);
            $constraint = app(GitInfoParser::class)->laravelConstraint($this->vendor, $this->package);

            $updates = [];

            // if the laravel version is different than what is in the database
            if ($this->current_laravel_version !== $currentVersion) {
                $updates['current_laravel_version'] = $currentVersion;
            }

            // if the current constraint is different than what is in the database
            if ($this->current_laravel_constraint !== $constraint) {
                $updates['current_laravel_constraint'] = $constraint;
            }

            $this->update($updates);
        } catch (RuntimeException $e) {
            // either the composer.json or composer.lock file doesn't exist for the repo
            if ($e->getCode() === 404) {
                $this->update(['ignored' => true]);
            }
        }
    }
}
