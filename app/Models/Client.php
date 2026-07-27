<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'client';
    protected $primaryKey = 'client_id';
    public $timestamps = false;

    protected $fillable = ['user_id', 'office', 'campus'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'client_id', 'client_id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'client_id', 'client_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id', 'client_id');
    }
}
