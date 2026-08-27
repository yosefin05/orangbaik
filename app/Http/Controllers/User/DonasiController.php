<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Services\MidtransService;
use App\Services\FlipService;
use App\Services\ManualTransferService;
use App\Http\Requests\DonasiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonasiController extends Controller
{
    public function __construct(
        protected MidtransService       $midtransService,
        protected FlipService           $flipService,
        protected ManualTransferService $manualTransferService,
    ) {}

    /**
     * Menampilkan halaman form donasi beserta pilihan payment channel
     */
    public function create($slug)
    {
        $campaign = Campaign::where('slug', $slug)
            ->with([
                'packages',
                'penggalangDana',
                'donasi.pembayaran',
            ])
            ->firstOrFail();

        $totalTerkumpul = $campaign->donasi
            ->filter(fn($d) => $d->pembayaran && $d->pembayaran->transaction_status === 'settlement')
            ->sum('nominal');

        $jumlahDonatur = $campaign->donasi
            ->filter(fn($d) => $d->pembayaran && $d->pembayaran->transaction_status === 'settlement')
            ->count();

        // Ambil payment channel aktif, urutkan berdasarkan sort_order, load gateway
        $paymentChannels = PaymentChannel::with('gateway')
            ->active()
            ->get();

        return view('pages.donasi-bayar', compact(
            'campaign',
            'totalTerkumpul',
            'jumlahDonatur',
            'paymentChannels'
        ));
    }

    /**
     * Menyimpan data donasi & membuat transaksi pembayaran di gateway yang sesuai
     *
     * Flow:
     * 1. Validasi input via DonasiRequest
     * 2. Ambil PaymentChannel & cek gateway-nya (Midtrans / Flip / Manual)
     * 3. Buat record Donasi (DB transaction)
     * 4. Buat record Pembayaran & dispatch ke service gateway yang sesuai
     * 5. Return response (snap_token / data VA / redirect URL)
     */
    public function store(DonasiRequest $request, $slug)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        // ============================================================
        // 1. CEK STATUS CAMPAIGN
        // ============================================================
        if (!$campaign->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign ini sudah tidak aktif dan tidak menerima donasi.',
            ], 422);
        }

        // ============================================================
        // 2. AMBIL PAYMENT CHANNEL
        // ============================================================
        $channelId = $request->payment_channel_id;
        $channel   = PaymentChannel::with('gateway')->findOrFail($channelId);

        if (!$channel->is_active || !$channel->gateway?->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran yang dipilih sedang tidak tersedia.',
            ], 422);
        }

        // Tentukan nama donatur
        $isAnonim    = $request->boolean('is_anonim', false);
        $namaDonatur = $isAnonim ? 'Hamba Allah' : ($request->nama_donatur ?: (auth()->user()?->name ?? 'Hamba Allah'));
        $nominal     = (int) $request->nominal;

        // ============================================================
        // 3. BUAT DATA DONASI
        // ============================================================
        DB::beginTransaction();

        try {
            $donasi = Donasi::create([
                'campaign_id'  => $campaign->id,
                'user_id'      => auth()->id(),
                'nama_donatur' => $namaDonatur,
                'email'        => auth()->user()?->email ?? null,
                'no_hp'        => $request->no_hp,
                'nominal'      => $nominal,
                'pesan_doa'    => $request->pesan,
                'is_anonim'    => $isAnonim,
            ]);

            // ============================================================
            // 4. ROUTING KE GATEWAY YANG SESUAI (Midtrans / Flip / Manual)
            // ============================================================
            $gatewayCode = $channel->gateway?->code;

            $result = match ($gatewayCode) {
                'midtrans' => $this->processMidtrans($donasi, $channel),
                'flip'     => $this->processFlip($donasi, $channel),
                'manual'   => $this->processManual($donasi, $channel),
                default    => throw new \Exception("Gateway '{$gatewayCode}' tidak dikenali."),
            };

            DB::commit();

            return response()->json(array_merge(['success' => true], $result));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Donasi store error', [
                'message'    => $e->getMessage(),
                'channel_id' => $channelId,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Proses donasi via Midtrans (QRIS, GoPay, ShopeePay)
     * Return: snap_token untuk Midtrans Snap JS
     */
    protected function processMidtrans(Donasi $donasi, PaymentChannel $channel): array
    {
        $orderId = $this->generateOrderId($donasi);

        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId,
            'transaction_status' => 'pending',
        ]);

        $snapToken = $this->midtransService->createTransaction($donasi, $pembayaran, $channel);

        return [
            'type'       => 'midtrans',
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
            'donasi_id'  => $donasi->id,
        ];
    }

    /**
     * Proses donasi via Flip (Virtual Account)
     * Return: data VA (bank, nomor VA, expired)
     */
    protected function processFlip(Donasi $donasi, PaymentChannel $channel): array
    {
        if (!$this->flipService->isConfigured()) {
            throw new \Exception('Layanan Virtual Account Flip belum tersedia. Silakan pilih metode pembayaran lain.');
        }

        $orderId = $this->generateOrderId($donasi);

        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId,
            'payment_type'       => 'va',
            'transaction_status' => 'pending',
        ]);

        $vaData = $this->flipService->createVirtualAccount($donasi, $pembayaran, $channel);

        return [
            'type'           => 'virtual_account',
            'order_id'       => $orderId,
            'donasi_id'      => $donasi->id,
            'bank_name'      => $channel->name,
            'account_number' => $vaData['account_number'] ?? null,
            'account_name'   => $channel->account_name ?? 'OrangBaik',
            'amount'         => $donasi->nominal,
            'expired_at'     => $vaData['expired_date'] ?? null,
            'redirect_url'   => route('donasi.bayar.instruksi', ['pembayaran' => $pembayaran->id]),
        ];
    }

    /**
     * Proses donasi via Transfer Manual
     * Return: redirect ke halaman instruksi transfer
     */
    protected function processManual(Donasi $donasi, PaymentChannel $channel): array
    {
        $pembayaran = $this->manualTransferService->createTransaction($donasi, $channel);

        return [
            'type'         => 'manual_transfer',
            'order_id'     => $pembayaran->order_id,
            'donasi_id'    => $donasi->id,
            'redirect_url' => route('donasi.bayar.instruksi', ['pembayaran' => $pembayaran->id]),
        ];
    }

    /**
     * Halaman instruksi pembayaran (VA / Transfer Manual)
     */
    public function instruksi(Pembayaran $pembayaran)
    {
        // Pastikan pembayaran milik user yang login (jika login)
        $donasi = $pembayaran->donasi()->with(['campaign', 'pembayaran.paymentChannel.gateway'])->firstOrFail();

        return view('pages.donasi-instruksi', compact('pembayaran', 'donasi'));
    }

    /**
     * Upload bukti transfer manual oleh donatur
     */
    public function uploadBukti(Request $request, Pembayaran $pembayaran)
    {
        $request->validate([
            'bukti_transfer' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:5120', // max 5MB
        ], [
            'bukti_transfer.required' => 'Bukti transfer wajib diupload.',
            'bukti_transfer.image'    => 'File harus berupa gambar.',
            'bukti_transfer.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        if ($pembayaran->transaction_status !== 'pending') {
            return back()->with('error', 'Pembayaran ini sudah diproses dan tidak dapat diubah.');
        }

        try {
            $this->manualTransferService->saveBuktiTransfer(
                $pembayaran,
                $request->file('bukti_transfer')
            );

            return back()->with('success', 'Bukti transfer berhasil diupload. Tim kami akan memverifikasi dalam 1x24 jam.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Halaman status pembayaran
     */
    public function status($status)
    {
        $allowed = ['sukses', 'pending', 'gagal'];
        if (!in_array($status, $allowed)) {
            abort(404);
        }

        return view('pages.donasi-status', compact('status'));
    }

    /**
     * Generate order ID unik.
     * Format: OB-{YYYYMMDD}-{donasi_id_padded}
     */
    protected function generateOrderId(Donasi $donasi): string
    {
        $prefix = config('payment.order_id_prefix', 'OB');
        $date   = now()->format('Ymd');
        $id     = str_pad($donasi->id, 5, '0', STR_PAD_LEFT);
        $random = strtoupper(\Illuminate\Support\Str::random(3));

        return "{$prefix}-{$date}-{$id}-{$random}";
    }
}