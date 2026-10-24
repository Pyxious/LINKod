<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectWorker extends Model
{
    protected $table = 'project_worker';
    protected $primaryKey = 'assignment_id';
    public $timestamps = false;

    protected $fillable = ['worker_id', 'project_id', 'date_assigned'];

    protected $casts = ['date_assigned' => 'date'];

    protected static function booted(): void
    {
        static::saved(function (self $assignment) {
            \App\Events\RequestUpdated::broadcastUpdate(null, (int) $assignment->project_id, 'worker_assigned');
        });

        static::deleted(function (self $assignment) {
            \App\Events\RequestUpdated::broadcastUpdate(null, (int) $assignment->project_id, 'worker_unassigned');
        });
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
}
