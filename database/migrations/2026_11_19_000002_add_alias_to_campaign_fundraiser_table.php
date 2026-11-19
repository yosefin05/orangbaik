<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_fundraiser', function (Blueprint $table) {
            $table->string('alias', 120)->nullable()->after('referral_code');
        });

        $rows = DB::table('campaign_fundraiser as cf')
            ->leftJoin('users as u', 'u.id', '=', 'cf.user_id')
            ->select('cf.id', 'cf.campaign_id', 'u.name')
            ->orderBy('cf.id')
            ->get();
        $used = [];
        foreach ($rows as $row) {
            $base = Str::slug($row->name ?: 'ajak-' . $row->campaign_id) ?: 'ajak';
            $alias = $base;
            $suffix = 1;
            while (isset($used[$alias]) || DB::table('campaign_fundraiser')->where('alias', $alias)->exists()) {
                $alias = $base . '-' . (++$suffix);
            }
            $used[$alias] = true;
            DB::table('campaign_fundraiser')->where('id', $row->id)->update(['alias' => $alias]);
        }

        Schema::table('campaign_fundraiser', function (Blueprint $table) {
            $table->unique('alias');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_fundraiser', function (Blueprint $table) {
            $table->dropUnique(['alias']);
            $table->dropColumn('alias');
        });
    }
};
