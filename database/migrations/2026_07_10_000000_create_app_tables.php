<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create all base application tables.
     * These tables were originally created but their migration files were removed.
     * This migration recreates them so ALTER TABLE migrations can run cleanly.
     */
    public function up(): void
    {
        // ── user ─────────────────────────────────────────────────────────
        // NOTE: totp_secret is added by 2026_07_18 migration
        //       email_hash is added by 2026_07_20 encrypt_pii migration
        //       email_account starts as TEXT (already widened) since this is a fresh install
        Schema::create('user', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username', 50)->unique();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('email_account', 100)->unique();
            $table->string('contact_number', 20)->nullable();
            $table->string('role', 20)->default('client');
            $table->string('password');
            $table->string('google_id', 100)->nullable()->unique();
            $table->string('avatar_url')->nullable();
        });

        // ── client ───────────────────────────────────────────────────────
        Schema::create('client', function (Blueprint $table) {
            $table->increments('client_id');
            $table->unsignedInteger('user_id');
            $table->string('office', 100)->nullable();
            $table->string('campus', 100)->nullable();
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
        });

        // ── staff ────────────────────────────────────────────────────────
        Schema::create('staff', function (Blueprint $table) {
            $table->increments('staff_id');
            $table->unsignedInteger('user_id');
            $table->string('role', 50)->nullable();
            $table->date('date_hired')->nullable();
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
        });

        // ── team_leader ──────────────────────────────────────────────────
        Schema::create('team_leader', function (Blueprint $table) {
            $table->increments('leader_id');
            $table->unsignedInteger('staff_id');
            $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
        });

        // ── team ─────────────────────────────────────────────────────────
        Schema::create('team', function (Blueprint $table) {
            $table->increments('team_id');
            $table->string('team_name', 100);
            $table->unsignedInteger('team_leader')->nullable();
            $table->integer('member_count')->default(0);
            $table->foreign('team_leader')->references('leader_id')->on('team_leader')->onDelete('set null');
        });

        // ── worker ───────────────────────────────────────────────────────
        Schema::create('worker', function (Blueprint $table) {
            $table->increments('worker_id');
            $table->unsignedInteger('staff_id');
            $table->unsignedInteger('team_id')->nullable();
            $table->date('date_hired')->nullable();
            $table->boolean('is_available')->default(true);
            $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
            $table->foreign('team_id')->references('team_id')->on('team')->onDelete('set null');
        });

        // ── category ─────────────────────────────────────────────────────
        Schema::create('category', function (Blueprint $table) {
            $table->increments('category_id');
            $table->string('category_name', 100);
            $table->text('description')->nullable();
        });

        // ── request ──────────────────────────────────────────────────────
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

        // ── request_history ──────────────────────────────────────────────
        Schema::create('request_history', function (Blueprint $table) {
            $table->increments('history_id');
            $table->unsignedInteger('request_id');
            $table->string('previous_status', 50)->nullable();
            $table->string('current_status', 50);
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('request_id')->references('request_id')->on('request')->onDelete('cascade');
            $table->foreign('updated_by')->references('user_id')->on('user')->onDelete('set null');
        });

        // ── project ──────────────────────────────────────────────────────
        Schema::create('project', function (Blueprint $table) {
            $table->increments('project_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('request_id')->nullable()->unique();
            $table->unsignedInteger('approved_by')->nullable();
            $table->date('date_approved')->nullable();
            $table->text('recommendation')->nullable();
            $table->foreign('client_id')->references('client_id')->on('client')->onDelete('cascade');
            $table->foreign('request_id')->references('request_id')->on('request')->onDelete('set null');
            $table->foreign('approved_by')->references('staff_id')->on('staff')->onDelete('set null');
        });

        // ── project_history ──────────────────────────────────────────────
        // NOTE: proof_attachment is added by 2026_07_18 migration
        Schema::create('project_history', function (Blueprint $table) {
            $table->increments('phistory_id');
            $table->unsignedInteger('project_id');
            $table->string('previous_status', 50)->nullable();
            $table->string('current_status', 50);
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('project_id')->references('project_id')->on('project')->onDelete('cascade');
            $table->foreign('updated_by')->references('user_id')->on('user')->onDelete('set null');
        });

        // ── project_worker ───────────────────────────────────────────────
        Schema::create('project_worker', function (Blueprint $table) {
            $table->increments('assignment_id');
            $table->unsignedInteger('worker_id');
            $table->unsignedInteger('project_id');
            $table->date('date_assigned')->nullable();
            $table->foreign('worker_id')->references('worker_id')->on('worker')->onDelete('cascade');
            $table->foreign('project_id')->references('project_id')->on('project')->onDelete('cascade');
        });

        // ── notification ─────────────────────────────────────────────────
        // NOTE: action_url is added by 2026_07_16 migration
        Schema::create('notification', function (Blueprint $table) {
            $table->increments('notification_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('sent_at')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('title', 200)->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
        });

        // ── user_log ─────────────────────────────────────────────────────
        // NOTE: action and ip_address are added by 2026_07_20_000002 migration
        Schema::create('user_log', function (Blueprint $table) {
            $table->increments('log_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
        });

        // ── evaluation ───────────────────────────────────────────────────
        Schema::create('evaluation', function (Blueprint $table) {
            $table->increments('evaluation_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('request_id')->nullable()->unique();
            $table->tinyInteger('rating')->unsigned()->nullable();
            $table->text('feedback_text')->nullable();
            $table->timestamp('rated_at')->nullable();
            $table->foreign('client_id')->references('client_id')->on('client')->onDelete('cascade');
            $table->foreign('request_id')->references('request_id')->on('request')->onDelete('set null');
        });

        // ── materials ────────────────────────────────────────────────────
        Schema::create('materials', function (Blueprint $table) {
            $table->increments('material_id');
            $table->string('material_name', 200);
            $table->string('unit_of_measurement', 50)->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0);
        });

        // ── bill_of_materials ────────────────────────────────────────────
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
        Schema::dropIfExists('request_history');
        Schema::dropIfExists('request');
        Schema::dropIfExists('category');
        Schema::dropIfExists('worker');
        Schema::dropIfExists('team');
        Schema::dropIfExists('team_leader');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('client');
        Schema::dropIfExists('user');
    }
};
