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

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
}
