<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Penggalang_Dana;
use App\Models\Berita;
use App\Models\Campaign_Fundraiser;
use App\Models\Komentar;
use App\Models\Filter;
use App\Models\Donasi;

use App\Models\Pembayaran;

class DashboardController extends Controller
{
    public function index()
    {
        $settlementQuery = Donasi::whereHas('pembayaran', fn($q) => $q->where('transaction_status', 'settlement'));
        $pendingQuery    = Donasi::whereHas('pembayaran', fn($q) => $q->where('transaction_status', 'pending'));
        $failedQuery     = Donasi::whereHas('pembayaran', fn($q) => $q->whereIn('transaction_status', ['failed', 'expired', 'expire']));

        return view('admin.dashboard', [
            'totalCampaign'         => Campaign::count(),
            'totalPenggalangDana'   => Penggalang_Dana::count(),
            'totalUser'             => User::where('role', 'user')->count(),
            'totalAdmin'            => User::where('role', 'admin')->count(),
            'totalFundraiser'       => Campaign_Fundraiser::count(),
            'totalBerita'           => Berita::count(),
            'totalKomentar'         => Komentar::count(),
            'totalFilter'           => Filter::count(),
            'totalTransaksi'        => Donasi::count(),
            'settlementTransaksi'   => (clone $settlementQuery)->count(),
            'pendingTransaksi'      => (clone $pendingQuery)->count(),
            'failedTransaksi'       => (clone $failedQuery)->count(),
            'totalSettlementAmount' => (clone $settlementQuery)->sum('nominal'),
        ]);
    }
}

