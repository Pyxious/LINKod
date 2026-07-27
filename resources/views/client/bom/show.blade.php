@extends('layouts.client')
@section('content')
<div style="background:#fff; padding:24px; border-radius:12px; width:100%; max-width:800px; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom:16px;">Bill of Materials for "{{ $project->request->title }}"</h2>
    <table style="width:100%; border-collapse:collapse; text-align:left;">
        <tr style="border-bottom:2px solid #eee;">
            <th style="padding:12px;">Material</th>
            <th>Quantity</th>
            <th>Cost</th>
        </tr>
        @forelse($project->billOfMaterials as $bom)
        <tr style="border-bottom:1px solid #eee;">
            <td style="padding:12px;">{{ $bom->material->material_name ?? 'Unknown' }}</td>
            <td>{{ $bom->qty }}</td>
            <td>₱{{ number_format($bom->total_cost, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="3" style="padding:12px; text-align:center;">No materials recorded.</td></tr>
        @endforelse
        <tr style="font-weight:bold; background:#f9fafb;">
            <td colspan="2" style="padding:12px; text-align:right;">Total:</td>
            <td>₱{{ number_format($totalCost, 2) }}</td>
        </tr>
    </table>
</div>
@endsection
