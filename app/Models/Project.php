<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';
    protected $primaryKey = 'project_id';
    public $timestamps = false;

    protected $fillable = [
        'client_id', 'request_id', 'approved_by', 'date_approved', 'recommendation',
    ];

    protected $casts = ['date_approved' => 'date'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id', 'request_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by', 'staff_id');
    }

    public function histories()
    {
        return $this->hasMany(ProjectHistory::class, 'project_id', 'project_id');
    }

    public function assignments()
    {
        return $this->hasMany(ProjectWorker::class, 'project_id', 'project_id');
    }

    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'project_worker', 'project_id', 'worker_id')
                    ->withPivot('date_assigned', 'assignment_id');
    }

    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterials::class, 'project_id', 'project_id');
    }

    public function latestHistory()
    {
        return $this->hasOne(ProjectHistory::class, 'project_id', 'project_id')
                    ->latestOfMany('updated_at');
    }

    public function getCurrentStatusAttribute(): string
    {
        return $this->latestHistory?->current_status ?? 'Pending';
    }
}
