<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use App\Models\PaymentGateway;
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

        $donasis = Donasi::with(['campaign.penggalangDana', 'pembayaran.paymentChannel.gateway'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $formattedDonations = $donasis->map(function ($donasi) {
            $pembayaran = $donasi->pembayaran;
            $trxStatus = strtolower((string) ($pembayaran?->transaction_status ?? 'pending'));
            $status = 'Menunggu';
            $statusKey = 'menunggu';
            $isResumable = false;
            $isSettled = $trxStatus === 'settlement';

            if ($isSettled) {
                $status = 'Selesai';
                $statusKey = 'selesai';
            } elseif ($trxStatus === 'pending') {
                $isResumable = true;
            } elseif (in_array($trxStatus, ['deny', 'cancel', 'expire', 'expired', 'failure', 'failed'], true)) {
                $status = 'Gagal';
                $statusKey = 'gagal';
            }

            return [
                'id' => $donasi->id,
                'pembayaran_id' => $pembayaran?->id,
                'is_resumable' => $isResumable,
                'resume_url' => $pembayaran ? route('donasi.resume', $pembayaran->id) : null,
                'type' => 'Donasi',
                'date' => $donasi->created_at?->format('d F Y') ?? '-',
                'status' => $status,
                'status_key' => $statusKey,
                'title' => $donasi->campaign->judul ?? 'Campaign tidak ditemukan',
                'organizer' => $donasi->campaign?->penggalangDana?->nama_penggalang ?? 'Penggalang Dana OrangBaik',
                'amount' => 'Rp' . number_format((int) $donasi->nominal, 0, ',', '.'),
                'amount_value' => $isSettled ? (int) $donasi->nominal : 0,
                'method' => $pembayaran?->paymentChannel?->name ?? ($pembayaran?->payment_type ?? 'Belum diketahui'),
                'invoice' => $pembayaran?->order_id ?? 'OB-XXXX',
                'image' => $donasi->campaign?->thumbnail,
            ];
        });

        $totalDonasi = $formattedDonations->count();
        // Financial totals only include payments confirmed as settlement.
        $totalNominal = $formattedDonations->sum('amount_value');
        $totalSelesai = $formattedDonations->where('status_key', 'selesai')->count();

        return view('pages.riwayat-donasi', compact(
            'formattedDonations',
            'totalDonasi',
            'totalNominal',
            'totalSelesai',
            'midtransGateway'
        ));
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
        if ($donasi->pembayaran?->transaction_status !== 'settlement') {
            abort(404, 'E-Kwitansi resmi hanya tersedia setelah pembayaran donasi berhasil dikonfirmasi.');
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
