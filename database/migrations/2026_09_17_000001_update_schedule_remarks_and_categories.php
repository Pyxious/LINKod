<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Category names to end with 'Services'
        $categories = DB::table('category')->get();
        foreach ($categories as $cat) {
            $name = trim($cat->category_name);
            if (!str_ends_with(strtolower($name), 'services')) {
                DB::table('category')
                    ->where('category_id', $cat->category_id)
                    ->update(['category_name' => $name . ' Services']);
            }
        }

        // 2. Normalize existing schedule approved / confirmed remarks in request_history
        $histories = DB::table('request_history')
            ->where(function ($query) {
                $query->where('remarks', 'LIKE', '%Request approved for%')
                      ->orWhere('remarks', 'LIKE', '%Visit schedule confirmed%')
                      ->orWhere('remarks', 'LIKE', '%confirmed visit schedule%');
            })
            ->get();

        foreach ($histories as $h) {
            if (preg_match('/(?:Request approved for|Visit schedule confirmed by client for|Admin confirmed visit schedule with client via phone for|Client confirmed visit schedule for)\s+([A-Za-z]+ \d{1,2}, \d{4})\s*\(([^)]+)\)/i', $h->remarks, $m)) {
                $date = $m[1];
                $rawWin = $m[2];
                $win = match(true) {
                    stripos($rawWin, 'morning') !== false || (stripos($rawWin, 'AM') !== false && stripos($rawWin, 'PM') === false) => 'Morning',
                    stripos($rawWin, 'afternoon') !== false || (stripos($rawWin, 'PM') !== false && stripos($rawWin, 'AM') === false) => 'Afternoon',
                    stripos($rawWin, 'whole') !== false || stripos($rawWin, 'AM-PM') !== false || stripos($rawWin, 'AM - PM') !== false => 'Whole Day',
                    default => trim($rawWin)
                };
                $newRemark = "GSO Team scheduled to visit the area on {$date} ({$win}).";
                DB::table('request_history')
                    ->where('history_id', $h->history_id)
                    ->update(['remarks' => $newRemark]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional reversal
    }
};
