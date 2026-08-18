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
        if (config('database.default') === 'pgsql') {
            // 1. Enable Full Replica Identity for accurate Realtime Change Data Capture (CDC)
            DB::statement('ALTER TABLE "notification" REPLICA IDENTITY FULL;');
            DB::statement('ALTER TABLE "request_messages" REPLICA IDENTITY FULL;');
            DB::statement('ALTER TABLE "request" REPLICA IDENTITY FULL;');
            DB::statement('ALTER TABLE "request_history" REPLICA IDENTITY FULL;');

            // 2. Add tables to Supabase Realtime publication
            DB::unprepared("
                DO $$
                BEGIN
                    BEGIN
                        ALTER PUBLICATION supabase_realtime ADD TABLE \"notification\";
                    EXCEPTION WHEN OTHERS THEN NULL;
                    END;
                    BEGIN
                        ALTER PUBLICATION supabase_realtime ADD TABLE \"request_messages\";
                    EXCEPTION WHEN OTHERS THEN NULL;
                    END;
                    BEGIN
                        ALTER PUBLICATION supabase_realtime ADD TABLE \"request\";
                    EXCEPTION WHEN OTHERS THEN NULL;
                    END;
                    BEGIN
                        ALTER PUBLICATION supabase_realtime ADD TABLE \"request_history\";
                    EXCEPTION WHEN OTHERS THEN NULL;
                    END;
                END $$;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
            DB::unprepared("
                DO $$
                BEGIN
                    BEGIN
                        ALTER PUBLICATION supabase_realtime DROP TABLE \"notification\";
                    EXCEPTION WHEN OTHERS THEN NULL;
                    END;
                    BEGIN
                        ALTER PUBLICATION supabase_realtime DROP TABLE \"request_messages\";
                    EXCEPTION WHEN OTHERS THEN NULL;
                    END;
                    BEGIN
                        ALTER PUBLICATION supabase_realtime DROP TABLE \"request\";
                    EXCEPTION WHEN OTHERS THEN NULL;
                    END;
                    BEGIN
                        ALTER PUBLICATION supabase_realtime DROP TABLE \"request_history\";
                    EXCEPTION WHEN OTHERS THEN NULL;
                    END;
                END $$;
            ");
        }
    }
};
