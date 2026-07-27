<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Materials;
use Illuminate\Http\Request;

class MaterialsController extends Controller
{
    public function index()
    {
        $materials = Materials::orderBy('material_name')->paginate(20);
        return view('admin.materials.index', compact('materials'));
    }

    public function create()
    {
        return view('admin.materials.form', ['material' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_name'       => 'required|string|max:150',
            'unit_of_measurement' => 'nullable|string|max:50',
            'unit_cost'           => 'required|numeric|min:0',
        ]);

        $material = Materials::create($validated);

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin added new material: {$validated['material_name']}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material added.');
    }

    public function edit(int $id)
    {
        $material = Materials::findOrFail($id);
        return view('admin.materials.form', compact('material'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'material_name'       => 'required|string|max:150',
            'unit_of_measurement' => 'nullable|string|max:50',
            'unit_cost'           => 'required|numeric|min:0',
        ]);

        Materials::findOrFail($id)->update($validated);

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin updated material #{$id}: {$validated['material_name']}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material updated.');
    }
}
