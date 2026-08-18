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
        if (Schema::hasTable('request_history') && !Schema::hasColumn('request_history', 'remarks')) {
            Schema::table('request_history', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('current_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('request_history') && Schema::hasColumn('request_history', 'remarks')) {
            Schema::table('request_history', function (Blueprint $table) {
                $table->dropColumn('remarks');
            });
        }
    }
};
