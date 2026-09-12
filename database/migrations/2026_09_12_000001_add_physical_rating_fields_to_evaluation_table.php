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
        if (Schema::hasTable('evaluation')) {
            Schema::table('evaluation', function (Blueprint $table) {
                if (!Schema::hasColumn('evaluation', 'proof_image_path')) {
                    $table->string('proof_image_path', 500)->nullable()->after('feedback_text');
                }
                if (!Schema::hasColumn('evaluation', 'rated_by_admin')) {
                    $table->boolean('rated_by_admin')->default(false)->after('proof_image_path');
                }
                if (!Schema::hasColumn('evaluation', 'admin_id')) {
                    $table->unsignedInteger('admin_id')->nullable()->after('rated_by_admin');
                    $table->foreign('admin_id')->references('user_id')->on('user')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('evaluation')) {
            Schema::table('evaluation', function (Blueprint $table) {
                if (Schema::hasColumn('evaluation', 'admin_id')) {
                    $table->dropForeign(['admin_id']);
                    $table->dropColumn('admin_id');
                }
                if (Schema::hasColumn('evaluation', 'rated_by_admin')) {
                    $table->dropColumn('rated_by_admin');
                }
                if (Schema::hasColumn('evaluation', 'proof_image_path')) {
                    $table->dropColumn('proof_image_path');
                }
            });
        }
    }
};
