@extends('layouts.admin')
@section('page-title', 'Bill of Materials')
@section('content')
<div style="background:#fff; padding:24px; border-radius:12px; width:100%; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
    <div style="display:flex; justify-content:space-between; margin-bottom:16px;">
        <h2 style="margin:0;">BOMs</h2>
        <a href="{{ route('admin.bom.create') }}" style="background:#1a3c8f; color:#fff; padding:8px 16px; border-radius:6px; text-decoration:none; font-size:14px;">+ Create BOM</a>
    </div>
    <table style="width:100%; border-collapse:collapse; text-align:left;">
        <tr style="border-bottom:2px solid #eee;">
            <th style="padding:12px;">Project ID</th>
            <th>Title</th>
            <th>Materials Count</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        @foreach($projects as $bomProject)
            @if($bomProject->billOfMaterials->count() > 0)
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:12px;">#{{ $bomProject->project_id }}</td>
                <td>{{ $bomProject->request->title ?? 'Untitled' }}</td>
                <td>{{ $bomProject->billOfMaterials->count() }}</td>
                <td>
                    @if($bomProject->billOfMaterials->whereNull('date_approved')->count() > 0)
                        <span style="color:#eab308; font-weight:600;">Pending Approval</span>
                    @else
                        <span style="color:#10b981; font-weight:600;">Approved</span>
                    @endif
                </td>
                <td><a href="{{ route('admin.bom.show', $bomProject->project_id) }}" style="color:#1a3c8f; text-decoration:none;">Review</a></td>
            </tr>
            @endif
        @endforeach
    </table>
</div>
@endsection
