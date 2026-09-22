<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Fundraiser;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Services\PaymentGatewayManager;
use App\Services\ManualTransferService;
use App\Http\Requests\DonasiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonasiController extends Controller
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected ManualTransferService $manualTransferService,
    ) {
    }

    /**
     * Menampilkan halaman form donasi beserta pilihan payment channel.
     */
    public function create($slug)
    {
        $campaign = Campaign::where('slug', $slug)
            ->orWhere('custom_slug', $slug)
            ->with([
                'packages',
                'penggalangDana',
                'donasi.pembayaran',
            ])
            ->firstOrFail();

        // Hanya kampanye aktif yang bisa didonasi
        if (!$campaign->is_active) {
            abort(404, 'Campaign ini tidak menerima donasi saat ini.');
        }

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
     * Menyimpan data donasi & membuat transaksi pembayaran di gateway yang sesuai.
     * Nominal sudah di-merge oleh DonasiRequest::prepareForValidation().
     * is_anonim sudah di-merge dari anonymous_donor/anonymous_message.
     */
    public function store(DonasiRequest $request, $slug)
    {
        $campaign = Campaign::where('slug', $slug)
            ->orWhere('custom_slug', $slug)
            ->firstOrFail();

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
        // 2. VALIDASI NOMINAL TERHADAP CAMPAIGN MINIMAL
        // ============================================================
        $nominal = (int) $request->nominal;
        $minimalDonasi = (int) ($campaign->minimal_donasi ?? 1000);

        if ($nominal < $minimalDonasi) {
            return response()->json([
                'success' => false,
                'errors'  => [
                    'nominal' => ["Minimal donasi untuk campaign ini adalah Rp " . number_format($minimalDonasi, 0, ',', '.')],
                ],
            ], 422);
        }

        // ============================================================
        // 3. AMBIL PAYMENT CHANNEL
        // ============================================================
        $channelId = $request->payment_channel_id;
        $channel = PaymentChannel::with('gateway')->findOrFail($channelId);

        if (!$channel->is_active || !$channel->gateway?->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran yang dipilih sedang tidak tersedia.',
            ], 422);
        }

        // ============================================================
        // 4. TENTUKAN IDENTITAS DONATUR
        // ============================================================
        $isAnonim = (bool) $request->is_anonim; // sudah di-merge oleh DonasiRequest

        if ($isAnonim) {
            $namaDonatur = 'Hamba Allah';
        } else {
            $namaDonatur = $request->nama_donatur
                ?: (auth()->user()?->name ?? 'Hamba Allah');
        }

        // Referral fundraiser
        $referralCode = $request->input('ref')
            ?: session('campaign_referral.' . $campaign->id);
        $fundraiserId = Campaign_Fundraiser::where('campaign_id', $campaign->id)
            ->where('referral_code', $referralCode)
            ->where('status', 'active')
            ->value('id');

        // ============================================================
        // 5. BUAT DATA DONASI & PEMBAYARAN
        // ============================================================
        DB::beginTransaction();

        try {
            $donasi = Donasi::create([
                'campaign_id'  => $campaign->id,
                'fundraiser_id'=> $fundraiserId,
                'user_id'      => auth()->id(),
                'nama_donatur' => $namaDonatur,
                'email'        => auth()->user()?->email ?? null,
                'no_hp'        => $request->no_hp,
                'nominal'      => $nominal,
                'pesan_doa'    => $request->pesan,
                'is_anonim'    => $isAnonim,
            ]);

            $orderId = $this->generateOrderId($donasi);

            $pembayaran = Pembayaran::create([
                'donasi_id'          => $donasi->id,
                'payment_channel_id' => $channel->id,
                'payment_token'      => (string) \Illuminate\Support\Str::uuid(),
                'order_id'           => $orderId,
                'payment_type'       => $channel->payment_type ?? 'instant',
                'transaction_status' => 'pending',
            ]);

            // ============================================================
            // 6. ROUTING DINAMIS VIA DRIVER PATTERN
            // ============================================================
            $driver = $this->gatewayManager->driver($channel->gateway);
            $result = $driver->createTransaction($donasi, $pembayaran, $channel);

            DB::commit();

return response()->json(array_merge([
    'success' => true,
    'payment_token' => $pembayaran->payment_token,
    'pembayaran_id' => $pembayaran->id,
], $result));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Donasi store error', [
                'message'    => $e->getMessage(),
                'channel_id' => $channelId,
                'campaign'   => $campaign->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran. Silakan coba lagi atau hubungi admin.',
            ], 500);
        }
    }

    /**
     * Halaman instruksi pembayaran (VA / Transfer Manual).
     * Authorization:
     * - User terautentikasi: diverifikasi berdasarkan ownership donasi (user_id).
     * - Guest: wajib menyertakan payment_token yang valid (mencegah enumerasi IDOR).
     */
    public function instruksi(Request $request, Pembayaran $pembayaran)
    {
        $pembayaran->load(['donasi.campaign', 'paymentChannel.gateway']);

        $donasi = $pembayaran->donasi;

        if (!$donasi) {
            abort(404, 'Data donasi tidak ditemukan.');
        }

        // Authorization check
        if (auth()->check()) {
            if ($donasi->user_id !== null && auth()->id() !== $donasi->user_id && !auth()->user()?->isAdmin()) {
                abort(403, 'Anda tidak memiliki akses ke halaman pembayaran ini.');
            }
            if ($donasi->user_id === null && !auth()->user()?->isAdmin()) {
                $token = $request->query('token');
                if (!$pembayaran->isValidGuestToken($token)) {
                    abort(403, 'Akses tidak diizinkan. Token pembayaran guest tidak valid atau tidak disertakan.');
                }
            }
        } else {
            if ($donasi->user_id !== null) {
                abort(403, 'Silakan login terlebih dahulu untuk mengakses halaman pembayaran ini.');
            }
            $token = $request->query('token');
            if (!$pembayaran->isValidGuestToken($token)) {
                abort(403, 'Akses tidak diizinkan. Token pembayaran guest tidak valid atau tidak disertakan.');
            }
        }

        return view('pages.donasi-instruksi', compact('pembayaran', 'donasi'));
    }

    /**
     * Upload bukti transfer donasi manual oleh donatur.
     * Authorization:
     * - User terautentikasi: diverifikasi berdasarkan ownership donasi (user_id).
     * - Guest: wajib menyertakan payment_token yang valid.
     */
    public function uploadBukti(Request $request, Pembayaran $pembayaran)
    {
        $pembayaran->load('donasi');
        $donasi = $pembayaran->donasi;

        if (!$donasi) {
            abort(404, 'Data donasi tidak ditemukan.');
        }

        // Authorization check
        if (auth()->check()) {
            if ($donasi->user_id !== null && auth()->id() !== $donasi->user_id && !auth()->user()?->isAdmin()) {
                abort(403, 'Anda tidak memiliki akses untuk mengunggah bukti pada pembayaran ini.');
            }
            if ($donasi->user_id === null && !auth()->user()?->isAdmin()) {
                $token = $request->input('token', $request->query('token'));
                if (!$pembayaran->isValidGuestToken($token)) {
                    abort(403, 'Akses tidak diizinkan. Token pembayaran guest tidak valid atau tidak disertakan.');
                }
            }
        } else {
            if ($donasi->user_id !== null) {
                abort(403, 'Silakan login terlebih dahulu untuk mengunggah bukti transfer ini.');
            }
            $token = $request->input('token', $request->query('token'));
            if (!$pembayaran->isValidGuestToken($token)) {
                abort(403, 'Akses tidak diizinkan. Token pembayaran guest tidak valid atau tidak disertakan.');
            }
        }

        // Jangan izinkan upload jika sudah settlement
        if ($pembayaran->transaction_status === 'settlement') {
            return back()->with('error', 'Pembayaran ini sudah lunas, bukti transfer tidak perlu diunggah.');
        }

        $request->validate([
            'bukti_transfer' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'bukti_transfer.required' => 'Pilih file bukti transfer terlebih dahulu.',
            'bukti_transfer.mimes'    => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'bukti_transfer.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $this->manualTransferService->saveBuktiTransfer($pembayaran, $request->file('bukti_transfer'));

            return back()->with('success', 'Bukti transfer berhasil diunggah. Tim OrangBaik akan memverifikasi dalam 1x24 jam.');

        } catch (\Exception $e) {
            Log::error('Upload bukti transfer gagal', [
                'pembayaran_id' => $pembayaran->id,
                'error'         => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengunggah bukti transfer. Silakan coba lagi.');
        }
    }

    /**
     * Resume / Lanjutkan pembayaran pending yang sudah ada (Authenticated User).
     * TIDAK membuat donasi baru dan TIDAK membuat pembayaran baru.
     */
    public function resume(Request $request, Pembayaran $pembayaran)
    {
        $pembayaran->load(['donasi.campaign', 'paymentChannel.gateway']);
        $donasi = $pembayaran->donasi;

        if (!$donasi) {
            abort(404, 'Data donasi tidak ditemukan.');
        }

        // Verifikasi kepemilikan donasi
        if ($donasi->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke transaksi donasi ini.');
        }

        // Jika sudah settlement, infokan bahwa transaksi telah selesai
        if ($pembayaran->isSettled()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'status'  => 'settlement',
                    'message' => 'Pembayaran donasi ini sudah berhasil diverifikasi.',
                ], 422);
            }
            return redirect()->route('riwayat.donasi')
                ->with('info', 'Pembayaran donasi #' . $pembayaran->order_id . ' sudah berhasil.');
        }

        // Jika tidak resumable (failed / expired)
        if (!$pembayaran->isResumable()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'status'  => $pembayaran->transaction_status,
                    'message' => 'Status pembayaran ini adalah ' . $pembayaran->status_label . ' dan tidak dapat dilanjutkan.',
                ], 422);
            }
            return redirect()->route('riwayat.donasi')
                ->with('warning', 'Pembayaran donasi #' . $pembayaran->order_id . ' sudah ' . $pembayaran->status_label . '.');
        }

        $gatewayCode = $pembayaran->paymentChannel?->gateway?->code;

        // Untuk Midtrans: kembalikan snap_token yang sudah ada
        if ($gatewayCode === 'midtrans' && !empty($pembayaran->snap_token)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'type'       => 'midtrans',
                    'snap_token' => $pembayaran->snap_token,
                    'order_id'   => $pembayaran->order_id,
                    'donasi_id'  => $donasi->id,
                ]);
            }

            return redirect()->route('donasi.bayar.instruksi', $pembayaran->id);
        }

        // Untuk Flip / Manual Transfer / Custom Gateway: arahkan ke halaman instruksi pembayaran yang ada
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'type'         => 'redirect',
                'redirect_url' => route('donasi.bayar.instruksi', $pembayaran->id),
                'order_id'     => $pembayaran->order_id,
                'donasi_id'    => $donasi->id,
            ]);
        }

        return redirect()->route('donasi.bayar.instruksi', $pembayaran->id);
    }

    /**
     * Halaman status pembayaran (sukses, pending, gagal).
     */
   public function status(Request $request, $status)
{
    $statusLabels = [
        'sukses' => [
            'title' => 'Pembayaran Berhasil',
            'icon' => 'bi-check-circle-fill',
            'color' => 'text-green',
        ],
        'pending' => [
            'title' => 'Pembayaran Menunggu',
            'icon' => 'bi-hourglass-split',
            'color' => 'text-orange',
        ],
        'gagal' => [
            'title' => 'Pembayaran Gagal',
            'icon' => 'bi-x-circle-fill',
            'color' => 'text-red',
        ],
    ];

    $info = $statusLabels[$status] ?? $statusLabels['pending'];

    $pembayaran = null;

    // Hanya cari transaksi ketika status pending
    // dan request membawa ID pembayaran + payment token.
    if (
        $status === 'pending' &&
        $request->filled('pembayaran') &&
        $request->filled('token')
    ) {
        $pembayaran = Pembayaran::with('donasi')
            ->where('id', $request->integer('pembayaran'))
            ->where('payment_token', $request->query('token'))
            ->where('transaction_status', 'pending')
            ->first();

        // Kalau transaksi milik user yang sudah login,
        // tetap boleh ditampilkan.
        //
        // Kalau transaksi guest (user_id NULL),
        // token yang valid sudah menjadi bukti akses.
        if (
            $pembayaran &&
            $pembayaran->donasi &&
            $pembayaran->donasi->user_id !== null &&
            $pembayaran->donasi->user_id !== auth()->id()
        ) {
            $pembayaran = null;
        }
    }

    return view('pages.donasi-status', compact(
        'status',
        'info',
        'pembayaran'
    ));
}

    /**
     * Generate Order ID unik untuk donasi.
     * Format: OB-YYYYMMDD-DONASI_ID-RANDOM
     */
    protected function generateOrderId(Donasi $donasi): string
    {
        $prefix = config('payment.order_id_prefix', 'OB');
        $date   = now()->format('Ymd');
        $rand   = strtoupper(substr(md5(uniqid()), 0, 4));

        return sprintf('%s-%s-%d-%s', $prefix, $date, $donasi->id, $rand);
    }
}