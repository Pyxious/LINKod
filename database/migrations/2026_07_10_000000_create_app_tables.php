<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Unified migration for all LINKod database tables, initial categories, and configuration.
     */
    public function up(): void
    {
        // ── sessions ──────────────────────────────────────────────────────
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // ── cache & cache_locks ──────────────────────────────────────────
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->bigInteger('expiration')->index();
            });
        }

        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->bigInteger('expiration')->index();
            });
        }

        // ── user ─────────────────────────────────────────────────────────
        if (!Schema::hasTable('user')) {
            Schema::create('user', function (Blueprint $table) {
                $table->increments('user_id');
                $table->string('username', 50)->unique();
                $table->text('first_name');
                $table->text('last_name');
                $table->text('middle_name')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->text('email_account');
                $table->string('email_hash', 64)->nullable()->index();
                $table->text('contact_number')->nullable();
                $table->string('role', 20)->default('client');
                $table->string('password');
                $table->string('totp_secret')->nullable();
                $table->string('google_id', 100)->nullable()->unique();
                $table->string('avatar_url')->nullable();
            });
        }

        // ── client ───────────────────────────────────────────────────────
        if (!Schema::hasTable('client')) {
            Schema::create('client', function (Blueprint $table) {
                $table->increments('client_id');
                $table->unsignedInteger('user_id');
                $table->string('office', 100)->nullable();
                $table->string('campus', 100)->nullable();
                $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
            });
        }

        // ── staff ────────────────────────────────────────────────────────
        if (!Schema::hasTable('staff')) {
            Schema::create('staff', function (Blueprint $table) {
                $table->increments('staff_id');
                $table->unsignedInteger('user_id');
                $table->string('role', 50)->nullable();
                $table->date('date_hired')->nullable();
                $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
            });
        }

        // ── team_leader ──────────────────────────────────────────────────
        if (!Schema::hasTable('team_leader')) {
            Schema::create('team_leader', function (Blueprint $table) {
                $table->increments('leader_id');
                $table->unsignedInteger('staff_id');
                $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
            });
        }

        // ── team ─────────────────────────────────────────────────────────
        if (!Schema::hasTable('team')) {
            Schema::create('team', function (Blueprint $table) {
                $table->increments('team_id');
                $table->string('team_name', 100);
                $table->unsignedInteger('team_leader')->nullable();
                $table->integer('member_count')->default(0);
                $table->foreign('team_leader')->references('leader_id')->on('team_leader')->onDelete('set null');
            });
        }

        // ── worker ───────────────────────────────────────────────────────
        if (!Schema::hasTable('worker')) {
            Schema::create('worker', function (Blueprint $table) {
                $table->increments('worker_id');
                $table->unsignedInteger('staff_id');
                $table->unsignedInteger('team_id')->nullable();
                $table->date('date_hired')->nullable();
                $table->boolean('is_available')->default(true);
                $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
                $table->foreign('team_id')->references('team_id')->on('team')->onDelete('set null');
            });
        }

        // ── category ─────────────────────────────────────────────────────
        if (!Schema::hasTable('category')) {
            Schema::create('category', function (Blueprint $table) {
                $table->increments('category_id');
                $table->string('category_name', 100);
                $table->text('description')->nullable();
            });
        }

        // ── request ──────────────────────────────────────────────────────
        if (!Schema::hasTable('request')) {
            Schema::create('request', function (Blueprint $table) {
                $table->increments('request_id');
                $table->unsignedInteger('client_id');
                $table->unsignedInteger('category_id')->nullable();
                $table->string('title', 200);
                $table->text('description')->nullable();
                $table->string('campus', 100)->nullable();
                $table->string('location', 200)->nullable();
                $table->string('complexity', 50)->nullable();
                $table->string('urgency', 50)->nullable();
                $table->string('priority', 50)->nullable();
                $table->string('attachment')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->foreign('client_id')->references('client_id')->on('client')->onDelete('cascade');
                $table->foreign('category_id')->references('category_id')->on('category')->onDelete('set null');
            });
        }

        // ── request_history ──────────────────────────────────────────────
        if (!Schema::hasTable('request_history')) {
            Schema::create('request_history', function (Blueprint $table) {
                $table->increments('history_id');
                $table->unsignedInteger('request_id');
                $table->string('previous_status', 50)->nullable();
                $table->string('current_status', 50);
                $table->text('remarks')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('request_id')->references('request_id')->on('request')->onDelete('cascade');
                $table->foreign('updated_by')->references('user_id')->on('user')->onDelete('set null');
            });
        }

        // ── request_messages ─────────────────────────────────────────────
        if (!Schema::hasTable('request_messages')) {
            Schema::create('request_messages', function (Blueprint $table) {
                $table->increments('message_id');
                $table->unsignedInteger('request_id');
                $table->unsignedInteger('sender_id');
                $table->text('message');
                $table->string('attachment')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                $table->foreign('request_id')->references('request_id')->on('request')->onDelete('cascade');
                $table->foreign('sender_id')->references('user_id')->on('user')->onDelete('cascade');
            });
        }

        // ── project ──────────────────────────────────────────────────────
        if (!Schema::hasTable('project')) {
            Schema::create('project', function (Blueprint $table) {
                $table->increments('project_id');
                $table->unsignedInteger('client_id');
                $table->unsignedInteger('request_id')->nullable()->unique();
                $table->unsignedInteger('approved_by')->nullable();
                $table->date('date_approved')->nullable();
                $table->text('recommendation')->nullable();
                $table->string('nature_of_work')->nullable();
                $table->foreign('client_id')->references('client_id')->on('client')->onDelete('cascade');
                $table->foreign('request_id')->references('request_id')->on('request')->onDelete('set null');
                $table->foreign('approved_by')->references('staff_id')->on('staff')->onDelete('set null');
            });
        }

        // ── project_history ──────────────────────────────────────────────
        if (!Schema::hasTable('project_history')) {
            Schema::create('project_history', function (Blueprint $table) {
                $table->increments('phistory_id');
                $table->unsignedInteger('project_id');
                $table->string('previous_status', 50)->nullable();
                $table->string('current_status', 50);
                $table->string('proof_attachment')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('project_id')->references('project_id')->on('project')->onDelete('cascade');
                $table->foreign('updated_by')->references('user_id')->on('user')->onDelete('set null');
            });
        }

        // ── project_worker ───────────────────────────────────────────────
        if (!Schema::hasTable('project_worker')) {
            Schema::create('project_worker', function (Blueprint $table) {
                $table->increments('assignment_id');
                $table->unsignedInteger('worker_id');
                $table->unsignedInteger('project_id');
                $table->date('date_assigned')->nullable();
                $table->foreign('worker_id')->references('worker_id')->on('worker')->onDelete('cascade');
                $table->foreign('project_id')->references('project_id')->on('project')->onDelete('cascade');
            });
        }

        // ── notification ─────────────────────────────────────────────────
        if (!Schema::hasTable('notification')) {
            Schema::create('notification', function (Blueprint $table) {
                $table->increments('notification_id');
                $table->unsignedInteger('user_id');
                $table->timestamp('sent_at')->nullable();
                $table->string('type', 50)->nullable();
                $table->string('title', 200)->nullable();
                $table->text('message')->nullable();
                $table->string('action_url')->nullable();
                $table->boolean('is_read')->default(false);
                $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
            });
        }

        // ── user_log ─────────────────────────────────────────────────────
        if (!Schema::hasTable('user_log')) {
            Schema::create('user_log', function (Blueprint $table) {
                $table->increments('log_id');
                $table->unsignedInteger('user_id');
                $table->string('action', 255)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
            });
        }

        // ── evaluation ───────────────────────────────────────────────────
        if (!Schema::hasTable('evaluation')) {
            Schema::create('evaluation', function (Blueprint $table) {
                $table->increments('evaluation_id');
                $table->unsignedInteger('client_id');
                $table->unsignedInteger('request_id')->nullable()->unique();
                $table->tinyInteger('rating')->unsigned()->nullable();
                $table->json('ratings_breakdown')->nullable();
                $table->boolean('show_name')->default(true);
                $table->text('feedback_text')->nullable();
                $table->timestamp('rated_at')->nullable();
                $table->foreign('client_id')->references('client_id')->on('client')->onDelete('cascade');
                $table->foreign('request_id')->references('request_id')->on('request')->onDelete('set null');
            });
        }

        // ── materials ────────────────────────────────────────────────────
        if (!Schema::hasTable('materials')) {
            Schema::create('materials', function (Blueprint $table) {
                $table->increments('material_id');
                $table->string('material_name', 200);
                $table->string('unit_of_measurement', 50)->nullable();
                $table->decimal('unit_cost', 10, 2)->default(0);
            });
        }

        // ── bill_of_materials ────────────────────────────────────────────
        if (!Schema::hasTable('bill_of_materials')) {
            Schema::create('bill_of_materials', function (Blueprint $table) {
                $table->increments('bom_id');
                $table->unsignedInteger('project_id');
                $table->unsignedInteger('material_id')->nullable();
                $table->decimal('qty', 10, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('fulfilled_by')->nullable();
                $table->date('date_approved')->nullable();
                $table->foreign('project_id')->references('project_id')->on('project')->onDelete('cascade');
                $table->foreign('material_id')->references('material_id')->on('materials')->onDelete('set null');
                $table->foreign('created_by')->references('staff_id')->on('staff')->onDelete('set null');
                $table->foreign('fulfilled_by')->references('staff_id')->on('staff')->onDelete('set null');
            });
        }

        // ── SEED INITIAL CATEGORIES DIRECTLY IN MIGRATION ────────────────
        $categories = [
            ['category_id' => 1, 'category_name' => 'Carpentry/Masonry/Electrical', 'description' => 'Carpentry, masonry, and electrical works.'],
            ['category_id' => 2, 'category_name' => 'Plumbing',                     'description' => 'Plumbing installation and repair services.'],
            ['category_id' => 3, 'category_name' => 'Painting',                     'description' => 'Interior and exterior painting services.'],
            ['category_id' => 4, 'category_name' => 'Janitorial',                   'description' => 'Cleaning, sanitation, and housekeeping services.'],
            ['category_id' => 5, 'category_name' => 'Manpower',                     'description' => 'General manpower and labor assistance.'],
            ['category_id' => 6, 'category_name' => 'Landscaping',                  'description' => 'Grounds maintenance, lawn care, and landscaping services.'],
        ];

        foreach ($categories as $cat) {
            DB::table('category')->updateOrInsert(
                ['category_id' => $cat['category_id']],
                ['category_name' => $cat['category_name'], 'description' => $cat['description']]
            );
        }

        // ── ENABLE SUPABASE REALTIME (IF POSTGRESQL) ─────────────────────
        if (DB::getDriverName() === 'pgsql') {
            $realtimeTables = ['request_messages', 'notification', 'request', 'request_history', 'project', 'project_history', 'evaluation'];
            foreach ($realtimeTables as $tbl) {
                try {
                    DB::statement("ALTER PUBLICATION supabase_realtime ADD TABLE {$tbl};");
                } catch (\Throwable $e) {
                    // Ignore if already in publication or publication not created
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('evaluation');
        Schema::dropIfExists('user_log');
        Schema::dropIfExists('notification');
        Schema::dropIfExists('project_worker');
        Schema::dropIfExists('project_history');
        Schema::dropIfExists('project');
        Schema::dropIfExists('request_messages');
        Schema::dropIfExists('request_history');
        Schema::dropIfExists('request');
        Schema::dropIfExists('category');
        Schema::dropIfExists('worker');
        Schema::dropIfExists('team');
        Schema::dropIfExists('team_leader');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('client');
        Schema::dropIfExists('user');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
    }
};
