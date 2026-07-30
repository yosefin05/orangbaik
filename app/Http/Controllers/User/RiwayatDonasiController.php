<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use Illuminate\Support\Facades\Auth;

class RiwayatDonasiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return view('pages.riwayat-donasi', ['formattedDonations' => [], 'totalDonasi' => 0, 'totalNominal' => 0, 'totalSelesai' => 0]);
        }

        $donasis = Donasi::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedDonations = $donasis->map(function ($donasi) {
            $status = 'Menunggu';
            $statusKey = 'menunggu';
            if ($donasi->pembayaran) {
                $trxStatus = $donasi->pembayaran->transaction_status;
                if (in_array($trxStatus, ['settlement', 'capture'])) {
                    $status = 'Selesai';
                    $statusKey = 'selesai';
                } elseif ($trxStatus == 'pending') {
                    $status = 'Menunggu';
                    $statusKey = 'menunggu';
                } elseif (in_array($trxStatus, ['deny', 'cancel', 'expire', 'failure'])) {
                    $status = 'Gagal';
                    $statusKey = 'gagal';
                }
            }

            return [
                'type' => 'Donasi',
                'date' => $donasi->created_at->format('d F Y'),
                'status' => $status,
                'status_key' => $statusKey,
                'title' => $donasi->campaign->judul ?? 'Campaign tidak ditemukan',
                'organizer' => 'Orang Baik', // fallback sementara
                'amount' => 'Rp' . number_format($donasi->nominal, 0, ',', '.'),
                'amount_value' => $donasi->nominal,
                'method' => $donasi->pembayaran->payment_type ?? 'Belum diketahui',
                'invoice' => $donasi->pembayaran->order_id ?? 'OB-XXXX',
                'image' => asset('assets/slide1.png'),
            ];
        });

        $totalDonasi = $formattedDonations->count();
        $totalNominal = $formattedDonations->sum('amount_value');
        $totalSelesai = $formattedDonations->where('status_key', 'selesai')->count();

        return view('pages.riwayat-donasi', compact('formattedDonations', 'totalDonasi', 'totalNominal', 'totalSelesai'));
    }
}