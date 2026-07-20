<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->enum('campaign_type', [
                'regular',
                'emergency',
                'sustainable'
            ])->default('regular');
            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected'
            ])->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users');
            $table->text('rejection_reason')->nullable();
        });
    }

    public function down()
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->dropColumn([
                'campaign_type',
                'approval_statis',
                'approved_at',
                'approved_by',
                'rejection_reason'
            ]);
        });
    }
};
