@extends('layouts.admin')
@section('page-title', 'Edit User Role')
@section('content')
<div style="background:#fff; padding:24px; border-radius:12px; max-width:400px; width:100%; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
    <h3 style="margin-bottom:8px;">{{ $user->first_name }} {{ $user->last_name }}</h3>
    <p style="color:#666; margin-bottom:20px; font-size:14px;">{{ $user->email_account }}</p>

    <form action="{{ route('admin.users.update', $user->user_id) }}" method="POST">
        @csrf
        @method('PUT')
        <label style="display:block; margin-bottom:12px;">Role
            <select name="role" style="width:100%; padding:8px; margin-top:4px;" required>
                <option value="client" {{ $user->role === 'client' ? 'selected' : '' }}>Client</option>
                <option value="worker" {{ $user->role === 'worker' ? 'selected' : '' }}>Worker</option>
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </label>
        <button type="submit" style="width:100%; background:#1a3c8f; color:#fff; padding:12px; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Save Changes</button>
    </form>
</div>
@endsection
