<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'team';
    protected $primaryKey = 'team_id';
    public $timestamps = false;

    protected $fillable = ['team_name', 'team_leader', 'member_count'];

    public function leader()
    {
        return $this->belongsTo(TeamLeader::class, 'team_leader', 'leader_id');
    }

    public function workers()
    {
        return $this->hasMany(Worker::class, 'team_id', 'team_id');
    }
}
