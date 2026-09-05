<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user')) {
            Schema::table('user', function (Blueprint $table) {
                if (!Schema::hasColumn('user', 'remember_token')) {
                    $table->rememberToken();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user')) {
            Schema::table('user', function (Blueprint $table) {
                if (Schema::hasColumn('user', 'remember_token')) {
                    $table->dropRememberToken();
                }
            });
        }
    }
};
