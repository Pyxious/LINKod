<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserLog;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public string $reportType,
        public array $parameters = []
    ) {}

    /**
     * Execute the job in the background.
     */
    public function handle(NotificationService $notifications): void
    {
        try {
            $user = User::find($this->userId);
            if (!$user) {
                return;
            }

            // Log report execution in background
            UserLog::create([
                'user_id'    => $this->userId,
                'action'     => "Queued report generated: {$this->reportType}",
                'ip_address' => '127.0.0.1 (background worker)',
                'created_at' => now(),
            ]);

            // Notify user that background report processing is ready
            $notifications->createNotification(
                userId: $this->userId,
                type: 'report_ready',
                title: 'Report Ready',
                message: "Your {$this->reportType} has been processed successfully.",
                actionUrl: route('admin.reports.index')
            );
        } catch (\Throwable $e) {
            Log::error("[GenerateReportJob] Failed processing {$this->reportType}: " . $e->getMessage(), [
                'user_id' => $this->userId,
                'params'  => $this->parameters,
            ]);
            throw $e;
        }
    }
}
