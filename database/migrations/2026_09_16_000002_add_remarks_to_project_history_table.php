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
        if (Schema::hasTable('project_history')) {
            Schema::table('project_history', function (Blueprint $table) {
                if (!Schema::hasColumn('project_history', 'remarks')) {
                    $table->text('remarks')->nullable()->after('proof_attachment');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_history')) {
            Schema::table('project_history', function (Blueprint $table) {
                if (Schema::hasColumn('project_history', 'remarks')) {
                    $table->dropColumn('remarks');
                }
            });
        }
    }
};
