<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $table = 'user_log';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'ip_address', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
