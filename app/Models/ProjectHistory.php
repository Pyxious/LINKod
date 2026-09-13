<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectHistory extends Model
{
    protected $table = 'project_history';
    protected $primaryKey = 'phistory_id';
    public $timestamps = false;

    protected $fillable = [
        'project_id', 'previous_status', 'current_status', 'updated_at', 'updated_by', 'proof_attachment', 'remarks',
    ];

    protected $casts = ['updated_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function getDisplayRemarksAttribute(): ?string
    {
        if ($this->remarks === null) {
            return null;
        }

        $remarks = $this->remarks;
        $remarks = str_ireplace('rejected', 'disapproved', $remarks);
        $remarks = str_ireplace('rejecting', 'disapproving', $remarks);
        $remarks = str_ireplace('reject', 'disapprove', $remarks);
        $remarks = str_ireplace('bill of materials', 'List of Materials', $remarks);
        $remarks = (string) preg_replace('/\bBOM\b/i', 'List of Materials', $remarks);
        $remarks = str_ireplace('pricing and verification', 'verification', $remarks);
        $remarks = str_ireplace('verified and priced', 'verified', $remarks);
        $remarks = (string) preg_replace('/\s*\(PHP\s*[\d,]+(\.\d{2})?\)/i', '', $remarks);
        $remarks = str_ireplace('materials/cash', 'materials', $remarks);

        return $remarks;
    }
}
