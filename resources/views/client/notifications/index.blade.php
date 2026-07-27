@extends('layouts.client')
@section('content')
<div style="background:#fff; padding:24px; border-radius:12px; width:100%; max-width:800px; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom:16px;">Notifications</h2>
    <ul style="list-style:none; padding:0;">
        @forelse($notifications as $n)
        <li style="padding:16px; border-bottom:1px solid #eee; background: {{ $n->is_read ? '#fff' : '#f0f4f8' }};">
            <div style="font-weight:600;">{{ $n->title }} <span style="font-size:12px; color:#666; font-weight:400; float:right;">{{ $n->sent_at }}</span></div>
            <div style="color:#444; margin-top:4px;">{{ $n->message }}</div>
        </li>
        @empty
        <li style="padding:16px; color:#666;">No notifications.</li>
        @endforelse
    </ul>
    <div style="margin-top:16px;">{{ $notifications->links() }}</div>
</div>
@endsection
