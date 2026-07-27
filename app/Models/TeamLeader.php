<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamLeader extends Model
{
    protected $table = 'team_leader';
    protected $primaryKey = 'leader_id';
    public $timestamps = false;

    protected $fillable = ['staff_id'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function team()
    {
        return $this->hasOne(Team::class, 'team_leader', 'leader_id');
    }
}
