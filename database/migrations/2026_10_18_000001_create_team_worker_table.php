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
        if (!Schema::hasTable('team_worker')) {
            Schema::create('team_worker', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('team_id');
                $table->unsignedInteger('worker_id');
                $table->timestamp('created_at')->nullable()->useCurrent();

                $table->foreign('team_id')->references('team_id')->on('team')->onDelete('cascade');
                $table->foreign('worker_id')->references('worker_id')->on('worker')->onDelete('cascade');
                $table->unique(['team_id', 'worker_id']);
            });

            // Populate existing worker -> team assignments into team_worker pivot
            $workers = DB::table('worker')->whereNotNull('team_id')->get(['worker_id', 'team_id']);
            foreach ($workers as $w) {
                // Check that team actually exists before inserting
                $teamExists = DB::table('team')->where('team_id', $w->team_id)->exists();
                if ($teamExists) {
                    DB::table('team_worker')->updateOrInsert([
                        'team_id'   => $w->team_id,
                        'worker_id' => $w->worker_id,
                    ], [
                        'created_at' => now(),
                    ]);
                }
            }

            // Sync member_count on teams
            $teams = DB::table('team')->get();
            foreach ($teams as $t) {
                $count = DB::table('team_worker')->where('team_id', $t->team_id)->count();
                DB::table('team')->where('team_id', $t->team_id)->update(['member_count' => $count]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_worker');
    }
};
