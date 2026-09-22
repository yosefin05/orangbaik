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

        $donasis = Donasi::with(['campaign', 'pembayaran.paymentChannel.gateway'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedDonations = $donasis->map(function ($donasi) {
            $pembayaran = $donasi->pembayaran;
            $status = 'Menunggu';
            $statusKey = 'menunggu';
            $isResumable = false;

            if ($pembayaran) {
                $trxStatus = $pembayaran->transaction_status;
                if (in_array($trxStatus, ['settlement', 'capture'])) {
                    $status = 'Selesai';
                    $statusKey = 'selesai';
                } elseif ($trxStatus === 'pending') {
                    $status = 'Menunggu';
                    $statusKey = 'menunggu';
                    $isResumable = true;
                } elseif (in_array($trxStatus, ['deny', 'cancel', 'expire', 'expired', 'failure', 'failed'])) {
                    $status = 'Gagal';
                    $statusKey = 'gagal';
                }
            }

            return [
                'id' => $donasi->id,
                'pembayaran_id' => $pembayaran?->id,
                'is_resumable' => $isResumable,
                'resume_url' => $pembayaran ? route('donasi.resume', $pembayaran->id) : null,
                'type' => 'Donasi',
                'date' => $donasi->created_at->format('d F Y'),
                'status' => $status,
                'status_key' => $statusKey,
                'title' => $donasi->campaign->judul ?? 'Campaign tidak ditemukan',
                'organizer' => 'Orang Baik', // fallback sementara
                'amount' => 'Rp' . number_format($donasi->nominal, 0, ',', '.'),
                'amount_value' => $donasi->nominal,
                'method' => $pembayaran?->paymentChannel?->name ?? ($pembayaran?->payment_type ?? 'Belum diketahui'),
                'invoice' => $pembayaran?->order_id ?? 'OB-XXXX',
                'image' => $donasi->campaign->thumbnail,
            ];
        });

        $totalDonasi = $formattedDonations->count();
        $totalNominal = $formattedDonations->sum('amount_value');
        $totalSelesai = $formattedDonations->where('status_key', 'selesai')->count();

        return view('pages.riwayat-donasi', compact('formattedDonations', 'totalDonasi', 'totalNominal', 'totalSelesai'));
    }

    public function kwitansi(Donasi $donasi)
    {
        if ($donasi->user_id !== auth()->id()) {
            abort(403);
        }

        $donasi->load(['campaign.penggalangDana', 'pembayaran', 'user']);

        return view('pages.kwitansi', compact('donasi'));
    }
}