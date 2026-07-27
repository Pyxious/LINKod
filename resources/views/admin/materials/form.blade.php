@extends('layouts.admin')
@section('page-title', isset($material) ? 'Edit Material' : 'Add Material')
@section('content')
<div style="background:#fff; padding:24px; border-radius:12px; max-width:500px; width:100%; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
    <form action="{{ isset($material) ? route('admin.materials.update', $material->material_id) : route('admin.materials.store') }}" method="POST" style="display:flex; flex-direction:column; gap:12px;">
        @csrf
        @if(isset($material)) @method('PUT') @endif
        
        <label>Name
            <input type="text" name="material_name" style="width:100%; padding:8px;" value="{{ $material->material_name ?? '' }}" required>
        </label>

        <label>Unit of Measurement (e.g. pcs, bags)
            <input type="text" name="unit_of_measurement" style="width:100%; padding:8px;" value="{{ $material->unit_of_measurement ?? '' }}">
        </label>
        
        <label>Unit Cost
            <input type="number" step="0.01" name="unit_cost" style="width:100%; padding:8px;" value="{{ $material->unit_cost ?? '' }}" required>
        </label>

        <button type="submit" style="background:#1a3c8f; color:#fff; padding:12px; border:none; border-radius:8px; cursor:pointer; font-weight:600; margin-top:8px;">{{ isset($material) ? 'Update' : 'Save' }}</button>
    </form>
</div>
@endsection
