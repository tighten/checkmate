<?php

namespace App;

use App\Exceptions\ComposerJsonFileNotFound;
use App\Exceptions\ComposerLockFileNotFound;
use App\Exceptions\NotALaravelProject;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'ignored' => 'boolean',
        'is_valid' => 'boolean',
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

    public function scopeIgnored($query)
    {
        return $query->where('ignored', 1);
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    public function syncLaravelVersionAndConstraint()
    {
        try {
            $currentVersion = app(GitInfoParser::class)->laravelVersion($this->vendor, $this->package);
            $constraint = app(GitInfoParser::class)->laravelConstraint($this->vendor, $this->package);

            $updates = [
                'last_synced_at' => now(),
            ];

            if ($this->current_laravel_version !== $currentVersion) {
                $updates['current_laravel_version'] = $currentVersion;
            }

            if ($this->current_laravel_constraint !== $constraint) {
                $updates['current_laravel_constraint'] = $constraint;
            }

            if (! $this->is_valid) {
                $updates['is_valid'] = true;
            }

            $this->update($updates);

        } catch (ComposerJsonFileNotFound | ComposerLockFileNotFound | NotALaravelProject $e) {
            $this->update([
                'is_valid' => false,
                'last_synced_at' => now(),
            ]);
        }
    }
}
