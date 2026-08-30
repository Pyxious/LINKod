<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    protected $table = 'team';
    protected $primaryKey = 'team_id';
    public $timestamps = false;

    protected $fillable = ['team_name', 'category_id', 'team_leader', 'member_count', 'deleted_at'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function leader()
    {
        return $this->belongsTo(TeamLeader::class, 'team_leader', 'leader_id');
    }

    public function workers()
    {
        return $this->hasMany(Worker::class, 'team_id', 'team_id');
    }
}
