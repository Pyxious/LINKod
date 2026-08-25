<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $table = 'evaluation';
    protected $primaryKey = 'evaluation_id';
    public $timestamps = false;

    protected $fillable = ['client_id', 'request_id', 'rating', 'ratings_breakdown', 'show_name', 'feedback_text', 'rated_at'];

    protected $casts = [
        'rated_at'          => 'datetime',
        'ratings_breakdown' => 'array',
        'show_name'         => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id', 'request_id');
    }

    /**
     * Returns individual function rating scores (1-5) keyed by function identifier.
     * Falls back to overall rating for legacy records.
     */
    public function getFunctionRatingsAttribute(): array
    {
        $breakdown = $this->ratings_breakdown;
        if (is_string($breakdown)) {
            $breakdown = json_decode($breakdown, true);
        }

        if (is_array($breakdown) && !empty($breakdown)) {
            return [
                'quality'      => (int) ($breakdown['quality'] ?? $this->rating ?? 5),
                'attitude'     => (int) ($breakdown['attitude'] ?? $this->rating ?? 5),
                'safety'       => (int) ($breakdown['safety'] ?? $this->rating ?? 5),
                'time'         => (int) ($breakdown['time'] ?? $this->rating ?? 5),
                'housekeeping' => (int) ($breakdown['housekeeping'] ?? $this->rating ?? 5),
            ];
        }

        $r = (int) ($this->rating ?? 5);
        return [
            'quality'      => $r,
            'attitude'     => $r,
            'safety'       => $r,
            'time'         => $r,
            'housekeeping' => $r,
        ];
    }
}
