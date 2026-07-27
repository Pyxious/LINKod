<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    // Named ServiceRequest to avoid collision with Laravel's Request class
    protected $table = 'request';
    protected $primaryKey = 'request_id';
    public $timestamps = false;

    protected $fillable = [
        'client_id', 'category_id', 'title', 'description',
        'campus', 'location', 'complexity', 'urgency', 'priority',
        'attachment', 'submitted_at',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function histories()
    {
        return $this->hasMany(RequestHistory::class, 'request_id', 'request_id');
    }

    public function evaluation()
    {
        return $this->hasOne(Evaluation::class, 'request_id', 'request_id');
    }

    public function project()
    {
        return $this->hasOne(Project::class, 'request_id', 'request_id');
    }

    public function latestHistory()
    {
        return $this->hasOne(RequestHistory::class, 'request_id', 'request_id')
                    ->latestOfMany('updated_at');
    }

    public function getCurrentStatusAttribute(): string
    {
        return $this->latestHistory?->current_status ?? 'Submitted';
    }
}
