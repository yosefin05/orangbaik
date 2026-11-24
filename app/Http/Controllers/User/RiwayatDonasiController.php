<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RiwayatDonasiController extends Controller
{
    public function index()
    {
        $midtransGateway = PaymentGateway::where('code', 'midtrans')->first();
        $user = auth()->user();
        if (!$user) {
            return view('pages.riwayat-donasi', [
                'formattedDonations' => [],
                'totalDonasi' => 0,
                'totalNominal' => 0,
                'totalSelesai' => 0,
                'midtransGateway' => $midtransGateway,
            ]);
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

        return view('pages.riwayat-donasi', compact('formattedDonations', 'totalDonasi', 'totalNominal', 'totalSelesai', 'midtransGateway'));
    }

    public function kwitansi(Donasi $donasi)
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->role === 'admin' || (method_exists($user, 'isAdmin') && $user->isAdmin()));
        $isOwner = $user && (int) $donasi->user_id === (int) $user->id;

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat kwitansi ini.');
        }

        $donasi->load(['campaign.penggalangDana', 'pembayaran.paymentChannel', 'user']);

        $pembayaran = $donasi->pembayaran;
        $trxStatus = $pembayaran?->transaction_status;

        // Kwitansi HANYA boleh diterbitkan untuk donasi yang benar-benar settlement
        if (!$pembayaran || !in_array($trxStatus, ['settlement', 'capture'])) {
            abort(404, 'E-Kwitansi resmi hanya diterbitkan untuk transaksi donasi yang status pembayarannya telah berhasil (Settlement).');
        }

        return view('pages.kwitansi', compact('donasi'));
    }

    public function kwitansiSigned(Request $request, Donasi $donasi)
    {
        abort_unless($request->hasValidSignature(), 403);

        $donasi->load(['campaign.penggalangDana', 'pembayaran.paymentChannel', 'user']);
        abort_unless($donasi->pembayaran?->transaction_status === 'settlement', 404);

        return view('pages.kwitansi', compact('donasi'));
    }
}
