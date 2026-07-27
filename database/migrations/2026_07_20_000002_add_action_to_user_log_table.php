<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_log', function (Blueprint $table) {
            $table->string('action', 255)->nullable()->after('user_id')
                  ->comment('Human-readable description of the logged action');
            $table->string('ip_address', 45)->nullable()->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('user_log', function (Blueprint $table) {
            $table->dropColumn(['action', 'ip_address']);
        });
    }
};
