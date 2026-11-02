<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $client = $user->client;

        $allClientRequests = $client
            ? $client->requests()->with(['latestHistory', 'category', 'evaluation'])->latest('submitted_at')->get()
            : collect();

        $activeRequests = $allClientRequests->take(5);

        $searchableRequests = $allClientRequests->map(function ($req) {
            $rawSt = strtolower($req->current_status ?? 'submitted');
            $dispSt = match ($rawSt) {
                'rejected' => 'Disapproved',
                'awaiting verification of bill of materials', 'awaiting verification of list of materials' => 'Approved',
                'bom verified (awaiting client approval)', 'list of materials verified (awaiting client approval)' => 'Materials Verified',
                'awaiting materials', 'waiting for materials' => 'Awaiting Materials',
                'materials arrived' => 'Materials Arrived',
                'pending verification' => 'Final Check',
                default => ucfirst($req->current_status ?? 'Submitted'),
            };
            $pillCls = match (true) {
                $rawSt === 'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                in_array($rawSt, ['cancelled', 'rejected']) => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                in_array($rawSt, ['in progress', 'pending verification']) => 'bg-blue-50 text-[#0038A8] border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
                ($req->bom_status === 'awaiting_client' || str_contains($rawSt, 'awaiting client approval')) => 'bg-indigo-50 text-indigo-700 border-indigo-300 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-700',
                default => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
            };

            $padded3 = str_pad((string) $req->request_id, 3, '0', STR_PAD_LEFT);
            $padded4 = str_pad((string) $req->request_id, 4, '0', STR_PAD_LEFT);
            $trackingCode = $req->tracking_code;
            $catName = $req->category?->display_name ?? $req->category?->category_name ?? '';
            $locationLabel = ($req->campus ?? 'BU Main') . ($req->location ? ' — ' . $req->location : '');

            return [
                'id'         => (int) $req->request_id,
                'code'       => $trackingCode,
                'prefix'     => strtolower(explode('-', $trackingCode)[0] ?? 'req'),
                'padded3'    => $padded3,
                'padded4'    => '#' . $padded4,
                'title'      => $req->title ?? ($catName ?: 'Service Request'),
                'location'   => $locationLabel,
                'status'     => $dispSt,
                'pillClass'  => $pillCls,
                'timeAgo'    => $req->submitted_at ? \Carbon\Carbon::parse($req->submitted_at)->diffForHumans() : '',
                'url'        => route('client.requests.show', $req->request_id),
                'searchBlob' => strtolower(implode(' ', [
                    $req->title ?? '',
                    $req->location ?? '',
                    $req->campus ?? '',
                    \App\Models\ServiceRequest::normalizeDescriptionForComparison($req->description),
                    $catName,
                    $req->category?->category_name ?? '',
                    $dispSt,
                    $rawSt,
                ])),
            ];
        })->values()->all();

        // Compute unread from a single notifications fetch
        $notifications = $user->notifications()->latest('sent_at')->take(10)->get();
        $unreadCount   = $notifications->where('is_read', false)->count();

        return view('client.dashboard', compact('user', 'activeRequests', 'searchableRequests', 'unreadCount'));
    }
}
