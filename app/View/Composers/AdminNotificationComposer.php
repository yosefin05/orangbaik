<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Penggalang_Dana;
use App\Models\Campaign;
use App\Models\Pembayaran;
use App\Models\Komentar;

class AdminNotificationComposer
{
    public function compose(View $view): void
    {
        $pendingPenggalangCount = Penggalang_Dana::where('status', 'pending')->count();

        $pendingCampaignCount = Campaign::whereIn('campaign_type', ['emergency', 'sustainable'])
            ->where('approval_status', 'pending')
            ->count();

        // Donasi/manual transfer pending yang punya bukti transfer atau butuh verifikasi
        $pendingManualTransferCount = Pembayaran::where('transaction_status', 'pending')
            ->whereNotNull('bukti_transfer')
            ->count();

        $pendingKomentarCount = 0; // if komentar table has moderation column, count pending here

        $totalAdminNotifications = $pendingPenggalangCount + $pendingCampaignCount + $pendingManualTransferCount + $pendingKomentarCount;

        $view->with('adminNavBadges', [
            'penggalang'       => $pendingPenggalangCount,
            'campaign'         => $pendingCampaignCount,
            'manual_transfer'  => $pendingManualTransferCount,
            'komentar'         => $pendingKomentarCount,
            'total'            => $totalAdminNotifications,
        ]);
    }
}
