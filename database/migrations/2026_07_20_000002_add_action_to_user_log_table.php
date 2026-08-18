<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('user_log', 'action')) {
            Schema::table('user_log', function (Blueprint $table) {
                $table->string('action', 255)->nullable()
                      ->comment('Human-readable description of the logged action');
                $table->string('ip_address', 45)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('user_log', function (Blueprint $table) {
            $table->dropColumn(['action', 'ip_address']);
        });
    }
};
