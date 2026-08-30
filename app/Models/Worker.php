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
}

