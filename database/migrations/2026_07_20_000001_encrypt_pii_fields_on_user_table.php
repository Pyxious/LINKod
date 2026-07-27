<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Steps:
     *  1. Widen PII columns to TEXT to accommodate AES-256 ciphertext.
     *  2. Drop the DB-level UNIQUE index on email_account (uniqueness is
     *     now enforced via the email_hash column at the application layer).
     *  3. Add email_hash (SHA-256) column used for fast, secure lookups.
     *  4. Re-encrypt every existing row's PII fields in place.
     */
    public function up(): void
    {
        // ── 1 & 2. Widen columns + drop unique — use raw SQL to bypass
        //    MySQL strict mode which rejects the legacy 0000-00-00
        //    date_of_birth value when Blueprint::change() re-validates rows.
        DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

        // Check whether the legacy unique index on email_account still exists
        // (it won't on a fresh schema that already uses email_hash for lookups)
        $indexExists = collect(DB::select("SHOW INDEX FROM `user` WHERE Key_name = 'email_account'"))->isNotEmpty();

        $dropIndexSql = $indexExists ? ',DROP INDEX `email_account`' : '';

        DB::statement("ALTER TABLE `user`
            MODIFY COLUMN `first_name`    TEXT         NOT NULL,
            MODIFY COLUMN `last_name`     TEXT         NOT NULL,
            MODIFY COLUMN `middle_name`   TEXT         NULL,
            MODIFY COLUMN `email_account` TEXT         NOT NULL
            {$dropIndexSql}
        ");

        DB::statement("SET SESSION sql_mode = ''"); // restore default

        // ── 3. Add email_hash column for indexed lookups ─────────────────
        Schema::table('user', function (Blueprint $table) {
            $table->string('email_hash', 64)->nullable()->unique()->after('email_account')
                  ->comment('SHA-256 of email_account — used for indexed lookups.');
        });

        // ── 4. Re-encrypt existing rows ──────────────────────────────────
        DB::table('user')->orderBy('user_id')->each(function ($row) {
            DB::table('user')->where('user_id', $row->user_id)->update([
                'first_name'    => $this->encryptIfPlain($row->first_name),
                'last_name'     => $this->encryptIfPlain($row->last_name),
                'middle_name'   => $row->middle_name
                    ? $this->encryptIfPlain($row->middle_name)
                    : null,
                'email_account' => $this->encryptIfPlain($row->email_account),
                'email_hash'    => hash('sha256', strtolower(trim(
                    $this->resolvePlainEmail($row->email_account)
                ))),
            ]);
        });
    }

    /**
     * Reverse the migrations — decrypt PII back to plaintext and restore
     * the original DB-level UNIQUE constraint on email_account.
     */
    public function down(): void
    {
        // Decrypt all rows back to plaintext first
        DB::table('user')->orderBy('user_id')->each(function ($row) {
            DB::table('user')->where('user_id', $row->user_id)->update([
                'first_name'    => $this->decryptIfEncrypted($row->first_name),
                'last_name'     => $this->decryptIfEncrypted($row->last_name),
                'middle_name'   => $row->middle_name
                    ? $this->decryptIfEncrypted($row->middle_name)
                    : null,
                'email_account' => $this->decryptIfEncrypted($row->email_account),
            ]);
        });

        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('email_hash');
        });

        DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

        DB::statement("ALTER TABLE `user`
            MODIFY COLUMN `first_name`    VARCHAR(50)  NOT NULL,
            MODIFY COLUMN `last_name`     VARCHAR(50)  NOT NULL,
            MODIFY COLUMN `middle_name`   VARCHAR(50)  NULL,
            MODIFY COLUMN `email_account` VARCHAR(100) NOT NULL,
            ADD UNIQUE KEY `email_account` (`email_account`)
        ");

        DB::statement("SET SESSION sql_mode = ''");
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /**
     * Encrypt the value only if it is still plaintext.
     */
    private function encryptIfPlain(string $value): string
    {
        try {
            Crypt::decryptString($value);
            return $value; // Already encrypted — leave it alone.
        } catch (\Exception) {
            return Crypt::encryptString($value); // Plaintext — encrypt it.
        }
    }

    /**
     * Decrypt the value only if it is encrypted ciphertext.
     */
    private function decryptIfEncrypted(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return $value; // Already plaintext.
        }
    }

    /**
     * Resolve the plaintext email whether the column is still plain or encrypted.
     */
    private function resolvePlainEmail(string $value): string
    {
        try {
            return Crypt::decryptString($value); // Was already encrypted.
        } catch (\Exception) {
            return $value; // Still plaintext.
        }
    }
};
