<?php

namespace App\Events;

use App\Models\Project;
use App\Models\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?int $requestId;
    public ?int $projectId;
    public string $action;
    public ?string $status;
    public ?string $projectStatus;
    public ?string $bomStatus;
    public int $timestamp;

    /**
     * Track queued updates per request lifecycle to coalesce multiple DB writes
     * (e.g. ServiceRequest + RequestHistory + Project + ProjectHistory + BillOfMaterials)
     * into a single broadcast after all writes complete.
     */
    protected static array $queuedUpdates = [];
    protected static bool $terminatingRegistered = false;

    public function __construct(
        ?int $requestId = null,
        ?int $projectId = null,
        string $action = 'updated',
        ?string $status = null,
        ?string $projectStatus = null,
        ?string $bomStatus = null
    ) {
        $this->requestId = $requestId;
        $this->projectId = $projectId;
        $this->action = $action;
        $this->status = $status;
        $this->projectStatus = $projectStatus;
        $this->bomStatus = $bomStatus;
        $this->timestamp = now()->timestamp;
    }

    /**
     * Queue a coalesced real-time broadcast for the given request/project.
     */
    public static function broadcastUpdate(?int $requestId = null, ?int $projectId = null, string $action = 'updated'): void
    {
        try {
            $key = ($requestId ?? 0) . ':' . ($projectId ?? 0);
            static::$queuedUpdates[$key] = [
                'request_id' => $requestId ? (int) $requestId : null,
                'project_id' => $projectId ? (int) $projectId : null,
                'action'     => $action,
            ];

            Cache::forget('admin_requests_table_kpis');
            Cache::forget('admin_dashboard_stats');
            Cache::forget('req_rec_month_rows_' . now()->format('Y-m'));

            if (!static::$terminatingRegistered) {
                static::$terminatingRegistered = true;
                app()->terminating(function () {
                    static::flushQueued();
                });
            }
        } catch (\Throwable $e) {
            // Do not break application flow if broadcasting fails
        }
    }

    /**
     * Check whether active WebSocket broadcasting is configured and reachable.
     */
    public static function isBroadcastingEnabled(): bool
    {
        $driver = config('broadcasting.default');
        if (!$driver || in_array($driver, ['null', 'log'], true)) {
            return false;
        }
        if ($driver === 'reverb') {
            $key = config('broadcasting.connections.reverb.key');
            $host = config('broadcasting.connections.reverb.options.host');
            if (empty($key) || empty($host)) {
                return false;
            }
            if (app()->environment('production') && in_array($host, ['localhost', '127.0.0.1'], true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Flush all queued updates immediately.
     */
    public static function flushQueued(): void
    {
        $updates = static::$queuedUpdates;
        static::$queuedUpdates = [];

        if (empty($updates) || !static::isBroadcastingEnabled()) {
            return;
        }

        foreach ($updates as $item) {
            try {
                $reqId = $item['request_id'];
                $projId = $item['project_id'];

                if (!$reqId && $projId) {
                    $reqId = Project::where('project_id', $projId)->value('request_id');
                }
                if ($reqId && !$projId) {
                    $projId = Project::where('request_id', $reqId)->value('project_id');
                }

                $req = $reqId ? ServiceRequest::with(['latestHistory', 'project.latestHistory'])->find($reqId) : null;
                $proj = (!$req && $projId) ? Project::with('latestHistory')->find($projId) : $req?->project;

                $status = $req?->current_status;
                $projectStatus = $proj?->current_status;
                $bomStatus = $req?->bom_status;

                broadcast(new self(
                    $reqId ? (int) $reqId : null,
                    $projId ? (int) $projId : null,
                    $item['action'],
                    $status,
                    $projectStatus,
                    $bomStatus
                ));
            } catch (\Throwable $e) {
                // Ignore broadcast transport errors when Reverb is not running
            }
        }
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('linkod.updates'),
            new PrivateChannel('requests.global'),
        ];

        if ($this->requestId) {
            $channels[] = new PrivateChannel('requests.' . $this->requestId);
        }

        if ($this->projectId) {
            $channels[] = new PrivateChannel('projects.' . $this->projectId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'RequestUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'request_id'     => $this->requestId,
            'project_id'     => $this->projectId,
            'action'         => $this->action,
            'status'         => $this->status,
            'project_status' => $this->projectStatus,
            'bom_status'     => $this->bomStatus,
            'timestamp'      => $this->timestamp,
        ];
    }
}
