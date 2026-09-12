<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\Campaign_Update;
use App\Models\Campaign_Fundraiser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenggalangDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $penggalang = $user->penggalangDana;

        if (!$penggalang) {
            return redirect()->route('profile.user')
                ->with('error', 'Anda harus memiliki profil Penggalang Dana untuk mengakses dashboard.');
        }

        if ($penggalang->status !== 'approved') {
            return redirect()->route('profile.user')
                ->with('warning', 'Profil Penggalang Dana Anda belum disetujui. Status saat ini: ' . strtoupper($penggalang->status));
        }

        // Campaigns milik penggalang ini
        $campaigns = Campaign::where('penggalang_dana_id', $penggalang->id)
            ->with(['kategori', 'donasi.pembayaran'])
            ->latest()
            ->get();

        $campaignIds = $campaigns->pluck('id');

        // Statistik Keseluruhan (Settlement Only)
        $totalCampaign = $campaigns->count();
        $campaignAktif = $campaigns->filter(fn($c) => $c->is_active && $c->isApproved())->count();

        // Total Dana Terkumpul (HANYA SETTLEMENT)
        $totalDanaTerkumpul = Donasi::whereIn('campaign_id', $campaignIds)
            ->whereHas('pembayaran', fn($q) => $q->where('transaction_status', 'settlement'))
            ->sum('nominal');

        // Total Donatur (HANYA SETTLEMENT)
        $totalDonatur = Donasi::whereIn('campaign_id', $campaignIds)
            ->whereHas('pembayaran', fn($q) => $q->where('transaction_status', 'settlement'))
            ->count();

        // Total Fundraiser di campaign-campaign penggalang ini
        $totalFundraiser = Campaign_Fundraiser::whereIn('campaign_id', $campaignIds)
            ->where('status', 'active')
            ->count();

        // Donasi Terbaru (Settlement Only)
        $recentDonations = Donasi::whereIn('campaign_id', $campaignIds)
            ->whereHas('pembayaran', fn($q) => $q->where('transaction_status', 'settlement'))
            ->with(['campaign', 'pembayaran'])
            ->latest()
            ->take(10)
            ->get();

        // Kabar Terbaru / Updates
        $recentUpdates = Campaign_Update::whereIn('campaign_id', $campaignIds)
            ->with('campaign')
            ->latest()
            ->take(5)
            ->get();

        return view('pages.penggalang-dashboard', compact(
            'penggalang',
            'campaigns',
            'totalCampaign',
            'campaignAktif',
            'totalDanaTerkumpul',
            'totalDonatur',
            'totalFundraiser',
            'recentDonations',
            'recentUpdates'
        ));
    }
}
