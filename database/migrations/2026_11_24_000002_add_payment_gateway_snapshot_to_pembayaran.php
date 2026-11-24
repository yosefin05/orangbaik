<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->foreignId('payment_gateway_id')->nullable()->after('payment_channel_id')
                ->constrained('payment_gateways')->restrictOnDelete();
        });

        // Some focused legacy test fixtures create a minimal payment_channels table.
        // Production/staging schemas have the provider FK; only backfill when it exists.
        if (Schema::hasTable('payment_channels') && Schema::hasColumn('payment_channels', 'payment_gateway_id')) {
            DB::table('pembayaran')
                ->join('payment_channels', 'payment_channels.id', '=', 'pembayaran.payment_channel_id')
                ->update(['pembayaran.payment_gateway_id' => DB::raw('payment_channels.payment_gateway_id')]);
        }
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_gateway_id');
        });
    }
};
