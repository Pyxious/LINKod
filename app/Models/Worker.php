<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $table = 'worker';
    protected $primaryKey = 'worker_id';
    public $timestamps = false;

    protected $fillable = ['staff_id', 'team_id', 'date_hired', 'is_available'];

    protected $casts = ['is_available' => 'boolean'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id')->withTrashed();
    }

    public function assignments()
    {
        return $this->hasMany(ProjectWorker::class, 'worker_id', 'worker_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_worker', 'worker_id', 'project_id')
                    ->withPivot('date_assigned', 'assignment_id');
    }

    // Shortcut to the user through staff
    public function user()
    {
        return $this->hasOneThrough(User::class, Staff::class,
            'staff_id', 'user_id', 'staff_id', 'user_id');
    }

    public function activeProjects()
    {
        return $this->projects()
            ->with('request.category', 'latestHistory')
            ->whereHas('latestHistory', function($lh) {
                $lh->whereNotIn('current_status', ['Completed', 'Cancelled']);
            });
    }

    public function getActiveProjectsCountAttribute(): int
    {
        return $this->projects()
            ->whereHas('latestHistory', function($lh) {
                $lh->whereNotIn('current_status', ['Completed', 'Cancelled']);
            })
            ->count();
    }

    public function recalculateAvailability(): bool
    {
        $hasActive = $this->projects()
            ->whereHas('latestHistory', function($lh) {
                $lh->whereNotIn('current_status', ['Completed', 'Cancelled']);
            })
            ->exists();

        $isAvailable = !$hasActive;
        $this->update(['is_available' => $isAvailable]);
        return $isAvailable;
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_worker', 'worker_id', 'team_id');
    }

    public function belongsToTeam(int $teamId): bool
    {
        if ($this->relationLoaded('teams')) {
            return $this->teams->contains('team_id', $teamId);
        }

        return $this->teams()->where('team.team_id', $teamId)->exists() || $this->team_id === $teamId;
    }

    protected static ?array $teamLeaderStaffMap = null;

    protected static function getTeamLeaderStaffMap(): array
    {
        if (static::$teamLeaderStaffMap !== null) {
            return static::$teamLeaderStaffMap;
        }

        static::$teamLeaderStaffMap = \Illuminate\Support\Facades\Cache::remember('team_leader_staff_map', 120, function () {
            return Team::with('leader')
                ->get()
                ->mapWithKeys(fn(Team $t) => [(int) $t->team_id => $t->leader?->staff_id ? (int) $t->leader->staff_id : null])
                ->all();
        });

        return static::$teamLeaderStaffMap;
    }

    public function isTeamLeader(?int $teamId = null): bool
    {
        if ($this->relationLoaded('staff') && $this->staff?->role === 'Team Leader') {
            return true;
        }

        if (!$this->staff_id) {
            return false;
        }

        $leaderMap = static::getTeamLeaderStaffMap();
        $staffId = (int) $this->staff_id;

        // If checking for a specific team
        if ($teamId !== null) {
            if (!$this->belongsToTeam($teamId)) {
                return false;
            }

            return ($leaderMap[$teamId] ?? null) === $staffId;
        }

        // Otherwise check if worker leads ANY unit they currently belong to
        $assignedTeamIds = $this->relationLoaded('teams')
            ? $this->teams->pluck('team_id')->toArray()
            : ($this->team_id ? [$this->team_id] : $this->teams()->pluck('team.team_id')->toArray());

        if (empty($assignedTeamIds) && $this->team_id) {
            $assignedTeamIds = [$this->team_id];
        }

        foreach ($assignedTeamIds as $tId) {
            if (($leaderMap[(int) $tId] ?? null) === $staffId) {
                return true;
            }
        }

        return false;
    }
}

