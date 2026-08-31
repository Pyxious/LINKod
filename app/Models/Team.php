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

    public function getIconUrlAttribute(): string
    {
        $name = strtolower($this->team_name ?? '');
        return match(true) {
            str_contains($name, 'carpentry') || str_contains($name, 'masonry') || str_contains($name, 'electrical') || str_contains($name, 'mechanical') || str_contains($name, 'cms') => asset('images/units/CMS.png'),
            str_contains($name, 'plumbing') => asset('images/units/Plumbing.png'),
            str_contains($name, 'painting') || str_contains($name, 'paint') => asset('images/units/Painting.png'),
            str_contains($name, 'janitor') || str_contains($name, 'js') || str_contains($name, 'clean') => asset('images/units/JS.png'),
            str_contains($name, 'manpower') || str_contains($name, 'event') => asset('images/units/Manpower.png'),
            str_contains($name, 'landscap') || str_contains($name, 'grass') || str_contains($name, 'garden') => asset('images/units/Landscaping.png'),
            default => asset('images/units/CMS.png')
        };
    }
}
