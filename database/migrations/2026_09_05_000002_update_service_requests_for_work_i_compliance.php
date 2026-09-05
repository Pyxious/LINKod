<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('request')) {
            Schema::table('request', function (Blueprint $table) {
                if (!Schema::hasColumn('request', 'scheduled_date')) {
                    $table->date('scheduled_date')->nullable()->after('submitted_at');
                }
                if (!Schema::hasColumn('request', 'scheduled_time_window')) {
                    $table->string('scheduled_time_window', 20)->nullable()->after('scheduled_date'); // AM, PM, AM-PM
                }
                if (!Schema::hasColumn('request', 'schedule_status')) {
                    $table->string('schedule_status', 50)->nullable()->after('scheduled_time_window'); // pending_client_approval, approved, declined
                }
                if (!Schema::hasColumn('request', 'schedule_decline_reason')) {
                    $table->text('schedule_decline_reason')->nullable()->after('schedule_status');
                }
                if (!Schema::hasColumn('request', 'bom_status')) {
                    $table->string('bom_status', 50)->nullable()->default('none')->after('schedule_decline_reason'); // none, awaiting_admin, awaiting_client, approved, declined
                }
            });
        }

        // Merge Categories: Unify Janitorial (#4) & Manpower (#5) into "Janitorial and Manpower Services"
        if (Schema::hasTable('category')) {
            // Update Category 4 to "Janitorial and Manpower Services"
            DB::table('category')->where('category_id', 4)->update([
                'category_name' => 'Janitorial and Manpower Services',
                'description'   => 'Cleaning, sanitation, housekeeping, venue setup, and general manpower assistance.',
                'deleted_at'    => null,
            ]);

            // Re-point any existing requests from category 5 to 4
            if (Schema::hasTable('request')) {
                DB::table('request')->where('category_id', 5)->update(['category_id' => 4]);
            }

            // Re-point any existing teams from category 5 to 4
            if (Schema::hasTable('team')) {
                DB::table('team')->where('category_id', 4)->update([
                    'team_name' => 'Janitorial & Manpower Team'
                ]);
                DB::table('team')->where('category_id', 5)->update([
                    'category_id' => 4,
                    'deleted_at'  => now(),
                ]);
            }

            // Soft-delete category 5 so it won't appear in list of 5 active services
            DB::table('category')->where('category_id', 5)->update([
                'deleted_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('request')) {
            Schema::table('request', function (Blueprint $table) {
                $columns = ['scheduled_date', 'scheduled_time_window', 'schedule_status', 'schedule_decline_reason', 'bom_status'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('request', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
