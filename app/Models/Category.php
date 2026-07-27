<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';
    protected $primaryKey = 'category_id';
    public $timestamps = false;

    protected $fillable = ['category_name', 'description'];

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'category_id', 'category_id');
    }
}
