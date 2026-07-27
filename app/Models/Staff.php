<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $timestamps = false;

    protected $fillable = ['user_id', 'role', 'date_hired'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function teamLeader()
    {
        return $this->hasOne(TeamLeader::class, 'staff_id', 'staff_id');
    }

    public function worker()
    {
        return $this->hasOne(Worker::class, 'staff_id', 'staff_id');
    }

    public function approvedProjects()
    {
        return $this->hasMany(Project::class, 'approved_by', 'staff_id');
    }

    public function createdBoms()
    {
        return $this->hasMany(BillOfMaterials::class, 'created_by', 'staff_id');
    }

    public function fulfilledBoms()
    {
        return $this->hasMany(BillOfMaterials::class, 'fulfilled_by', 'staff_id');
    }
}
