@extends('layouts.admin')
@section('page-title', 'BOM Details')
@section('content')
<div style="background:#fff; padding:24px; border-radius:12px; width:100%; max-width:800px; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom:16px;">BOM for Project #{{ $project->project_id }}</h2>
    <table style="width:100%; border-collapse:collapse; text-align:left;">
        <tr style="border-bottom:2px solid #eee;">
            <th style="padding:12px;">Material</th>
            <th>Quantity</th>
            <th>Cost</th>
            <th>Status</th>
        </tr>
        @foreach($bomItems as $bom)
        <tr style="border-bottom:1px solid #eee;">
            <td style="padding:12px;">{{ $bom->material->material_name ?? 'Unknown' }}</td>
            <td>{{ $bom->qty }}</td>
            <td>₱{{ number_format($bom->total_cost, 2) }}</td>
            <td>
                @if($bom->is_approved)
                    <span style="color:#10b981; font-weight:600;">Approved</span>
                @else
                    <span style="color:#eab308; font-weight:600;">Pending</span>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
    
    @if($bomItems->where('is_approved', 0)->count() > 0)
    <form action="{{ route('admin.bom.approve', $project->project_id) }}" method="POST" style="margin-top:24px;">
        @csrf
        <button type="submit" style="background:#10b981; color:#fff; padding:12px 24px; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Approve All Pending Items</button>
    </form>
    @endif
</div>
@endsection
