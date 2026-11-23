<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->timestamp('expired_at')->nullable()->after('paid_at');
            $table->foreignId('expired_by')
                ->nullable()
                ->after('expired_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('expiration_reason')->nullable()->after('expired_by');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['expired_by']);
            $table->dropColumn(['expired_at', 'expired_by', 'expiration_reason']);
        });
    }
};
