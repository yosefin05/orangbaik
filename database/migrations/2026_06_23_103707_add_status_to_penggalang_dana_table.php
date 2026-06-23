<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table) {

            $table->enum(
                'status',
                [
                    'pending',
                    'approved',
                    'rejected'
                ]
            )->default('pending');
            $table->timestamp('verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table) {

            $table->dropColumn('status');
            $table->dropColumn('verified_at');

        });
    }
};