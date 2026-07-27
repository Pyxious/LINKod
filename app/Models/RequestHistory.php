<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestHistory extends Model
{
    protected $table = 'request_history';
    protected $primaryKey = 'history_id';
    public $timestamps = false;

    protected $fillable = [
        'request_id', 'previous_status', 'current_status', 'updated_at', 'updated_by',
    ];

    protected $casts = ['updated_at' => 'datetime'];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id', 'request_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }
}
