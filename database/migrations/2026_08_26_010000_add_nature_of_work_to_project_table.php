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
        if (Schema::hasTable('project') && !Schema::hasColumn('project', 'nature_of_work')) {
            Schema::table('project', function (Blueprint $table) {
                $table->string('nature_of_work', 150)->nullable()->after('recommendation');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project') && Schema::hasColumn('project', 'nature_of_work')) {
            Schema::table('project', function (Blueprint $table) {
                $table->dropColumn('nature_of_work');
            });
        }
    }
};
