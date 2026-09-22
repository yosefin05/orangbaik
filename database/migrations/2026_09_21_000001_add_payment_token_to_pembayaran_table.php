<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Add payment_token to pembayaran for secure guest payment authorization.
     *
     * Design decisions:
     * - Column is nullable to stay backward compatible with the backfill approach.
     * - Existing rows are backfilled with unique UUIDs before the unique index is added.
     * - The application layer always generates a token on new Pembayaran creation.
     * - Unique index ensures no two payments share a token.
     */
    public function up(): void
    {
        // Step 1: Add the column as nullable (no unique constraint yet, to allow batch backfill)
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('payment_token', 64)->nullable()->after('donasi_id');
        });

        // Step 2: Backfill existing rows with unique UUIDs
        // Process in chunks to be safe with large datasets
        DB::table('pembayaran')
            ->orderBy('id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('pembayaran')
                        ->where('id', $row->id)
                        ->update(['payment_token' => (string) Str::uuid()]);
                }
            });

        // Step 3: Add unique index after backfill completes (all rows now have a value)
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->unique('payment_token');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropUnique(['payment_token']);
            $table->dropColumn('payment_token');
        });
    }
};
