<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add softDeletes to category table
        if (Schema::hasTable('category')) {
            Schema::table('category', function (Blueprint $table) {
                if (!Schema::hasColumn('category', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Add category_id and softDeletes to team table
        if (Schema::hasTable('team')) {
            Schema::table('team', function (Blueprint $table) {
                if (!Schema::hasColumn('team', 'category_id')) {
                    $table->unsignedInteger('category_id')->nullable()->after('team_name');
                }
                if (!Schema::hasColumn('team', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Map existing teams to categories
            $teams = DB::table('team')->get();
            $categories = DB::table('category')->get();

            foreach ($teams as $team) {
                if (!$team->category_id) {
                    // Try to find matching category by exact or partial name
                    $teamLower = strtolower($team->team_name);
                    $matchedCat = $categories->first(function($cat) use ($teamLower) {
                        $catLower = strtolower($cat->category_name);
                        return str_contains($teamLower, $catLower) || str_contains($catLower, $teamLower)
                            || (str_contains($teamLower, 'carpentry') && str_contains($catLower, 'carpentry'))
                            || (str_contains($teamLower, 'plumb') && str_contains($catLower, 'plumb'))
                            || (str_contains($teamLower, 'paint') && str_contains($catLower, 'paint'))
                            || (str_contains($teamLower, 'janitor') && str_contains($catLower, 'janitor'))
                            || (str_contains($teamLower, 'manpower') && str_contains($catLower, 'manpower'))
                            || (str_contains($teamLower, 'landscape') && str_contains($catLower, 'landscape'));
                    });

                    if ($matchedCat) {
                        DB::table('team')->where('team_id', $team->team_id)->update([
                            'category_id' => $matchedCat->category_id,
                        ]);
                    } elseif (DB::table('category')->where('category_id', $team->team_id)->exists()) {
                        DB::table('team')->where('team_id', $team->team_id)->update([
                            'category_id' => $team->team_id,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('team')) {
            Schema::table('team', function (Blueprint $table) {
                if (Schema::hasColumn('team', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
                if (Schema::hasColumn('team', 'category_id')) {
                    $table->dropColumn('category_id');
                }
            });
        }

        if (Schema::hasTable('category')) {
            Schema::table('category', function (Blueprint $table) {
                if (Schema::hasColumn('category', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
