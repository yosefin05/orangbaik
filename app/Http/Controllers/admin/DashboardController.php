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

        // Statistik pembayaran dihitung dari tabel pembayaran agar setiap
        // transaksi hanya dihitung satu kali dan status pending tidak masuk
        // ke denominator tingkat keberhasilan.
        $settlementTransaksi = Pembayaran::where('transaction_status', 'settlement')->count();
        $pendingTransaksi = Pembayaran::where('transaction_status', 'pending')->count();
        $failedTransaksi = Pembayaran::whereIn('transaction_status', ['failed', 'expired', 'expire'])->count();
        $totalFinalizedTransaksi = Pembayaran::whereIn('transaction_status', [
            'settlement', 'failed', 'expired', 'expire',
        ])->count();

        $paymentSuccessRate = $totalFinalizedTransaksi > 0
            ? round(($settlementTransaksi / $totalFinalizedTransaksi) * 100, 1)
            : 0;

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
            'settlementTransaksi'   => $settlementTransaksi,
            'pendingTransaksi'      => $pendingTransaksi,
            'failedTransaksi'       => $failedTransaksi,
            'paymentSuccessRate'    => $paymentSuccessRate,
            'totalFinalizedTransaksi' => $totalFinalizedTransaksi,
            'totalSettlementAmount' => (clone $settlementQuery)->sum('nominal'),
        ]);
    }
}

