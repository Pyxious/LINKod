<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $table = 'evaluation';
    protected $primaryKey = 'evaluation_id';
    public $timestamps = false;

    protected $fillable = ['client_id', 'request_id', 'rating', 'feedback_text', 'rated_at'];

    protected $casts = ['rated_at' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id', 'request_id');
    }
}
