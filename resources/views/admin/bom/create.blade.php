@extends('layouts.admin')
@section('page-title', 'Create Bill of Materials')
@section('content')
<div style="background:#fff; padding:24px; border-radius:12px; max-width:600px; width:100%; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
    <h3 style="margin-bottom:16px;">Add Material to Project</h3>
    <form action="{{ route('admin.bom.store') }}" method="POST" style="display:flex; flex-direction:column; gap:12px;">
        @csrf
        <label>Project
            <select name="project_id" style="width:100%; padding:8px;" required>
                @foreach($projects as $p)
                    <option value="{{ $p->project_id }}">{{ $p->request->title ?? 'Project #'.$p->project_id }}</option>
                @endforeach
            </select>
        </label>
        
        <label>Material
            <select name="material_id" style="width:100%; padding:8px;" required>
                @foreach($materials as $m)
                    <option value="{{ $m->material_id }}">{{ $m->material_name }} (₱{{ $m->unit_cost }})</option>
                @endforeach
            </select>
        </label>

        <label>Quantity
            <input type="number" name="qty" min="1" value="1" style="width:100%; padding:8px;" required>
        </label>

        <button type="submit" style="background:#1a3c8f; color:#fff; padding:12px; border:none; border-radius:8px; cursor:pointer; font-weight:600; margin-top:8px;">Add to BOM</button>
    </form>
</div>
@endsection
