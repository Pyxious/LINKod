<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materials extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'material_id';
    public $timestamps = false;

    protected $fillable = ['material_name', 'unit_of_measurement', 'unit_cost'];

    protected $casts = ['unit_cost' => 'decimal:2'];

    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterials::class, 'material_id', 'material_id');
    }
}
