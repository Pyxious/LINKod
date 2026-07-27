<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillOfMaterials extends Model
{
    protected $table = 'bill_of_materials';
    protected $primaryKey = 'bom_id';
    public $timestamps = false;

    protected $fillable = [
        'project_id', 'material_id', 'qty', 'total_cost',
        'created_by', 'fulfilled_by', 'date_approved',
    ];

    protected $casts = [
        'qty'          => 'decimal:2',
        'total_cost'   => 'decimal:2',
        'date_approved'=> 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function material()
    {
        return $this->belongsTo(Materials::class, 'material_id', 'material_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by', 'staff_id');
    }

    public function fulfilledBy()
    {
        return $this->belongsTo(Staff::class, 'fulfilled_by', 'staff_id');
    }
}
