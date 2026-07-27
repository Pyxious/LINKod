<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = UserLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.audit.index', compact('logs'));
    }
}
