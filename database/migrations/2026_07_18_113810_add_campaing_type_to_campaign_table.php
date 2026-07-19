<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->enum('campaign_type', ['regular', 'emergency', 'sustainable'])->default('regular')->after('kategori_id');
            $table->enum('emergency_approval', ['pending', 'approved', 'rejected'])->nullable()->after('campaign_type');
            $table->timestamp('emergency_approved_at')->nullable()->after('emergency_approval');
            $table->foreignId('emergency_approved_by')->nullable()->constrained('users')->after('emergency_approved_at');
            $table->text('emergency_rejection_reason')->nullable()->after('emergency_approved_by');
        });
    }

    public function down()
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->dropColumn([
                'campaign_type',
                'emergency_approval',
                'emergency_approved_at',
                'emergency_approved_by',
                'emergency_rejection_reason'
            ]);
        });
    }
};
