<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectHistory extends Model
{
    protected $table = 'project_history';
    protected $primaryKey = 'phistory_id';
    public $timestamps = false;

    protected $fillable = [
        'project_id', 'previous_status', 'current_status', 'updated_at', 'updated_by', 'proof_attachment',
    ];

    protected $casts = ['updated_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }
}
