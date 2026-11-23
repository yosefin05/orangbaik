<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_fundraiser', function (Blueprint $table) {
            $table->string('short_alias', 120)->nullable()->unique()->after('alias');
        });

        Schema::table('campaign_fundraiser_alias', function (Blueprint $table) {
            $table->string('alias_type', 20)->default('referral')->after('alias');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_fundraiser_alias', function (Blueprint $table) {
            $table->dropColumn('alias_type');
        });

        Schema::table('campaign_fundraiser', function (Blueprint $table) {
            $table->dropUnique(['short_alias']);
            $table->dropColumn('short_alias');
        });
    }
};
