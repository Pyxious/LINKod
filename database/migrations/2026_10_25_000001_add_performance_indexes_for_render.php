<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use IF NOT EXISTS via raw PostgreSQL/SQLite compatible statements or Schema builder with try/catch
        $indexes = [
            'CREATE INDEX IF NOT EXISTS idx_rh_req_history_id ON request_history (request_id, history_id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_ph_proj_phistory_id ON project_history (project_id, phistory_id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_ph_current_status ON project_history (current_status)',
            'CREATE INDEX IF NOT EXISTS idx_notif_user_read_sent ON notification (user_id, is_read, sent_at DESC)',
            'CREATE INDEX IF NOT EXISTS idx_rm_req_read_sender ON request_messages (request_id, is_read, sender_id)',
            'CREATE INDEX IF NOT EXISTS idx_pw_project_worker ON project_worker (project_id, worker_id)',
        ];

        foreach ($indexes as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // Ignore if index already exists or table is missing in partial environments
            }
        }
    }

    public function down(): void
    {
        $drops = [
            'DROP INDEX IF EXISTS idx_rh_req_history_id',
            'DROP INDEX IF EXISTS idx_ph_proj_phistory_id',
            'DROP INDEX IF EXISTS idx_ph_current_status',
            'DROP INDEX IF EXISTS idx_notif_user_read_sent',
            'DROP INDEX IF EXISTS idx_rm_req_read_sender',
            'DROP INDEX IF EXISTS idx_pw_project_worker',
        ];

        foreach ($drops as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};
