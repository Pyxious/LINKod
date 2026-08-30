<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'category';
    protected $primaryKey = 'category_id';
    public $timestamps = false;

    protected $fillable = ['category_name', 'description', 'deleted_at'];

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'category_id', 'category_id');
    }

    public function team()
    {
        return $this->hasOne(Team::class, 'category_id', 'category_id');
    }
}
