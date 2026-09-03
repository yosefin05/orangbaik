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
     * Menampilkan halaman form donasi beserta pilihan payment channel
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
     */
    public function store(DonasiRequest $request, $slug)
    {
        $campaign = Campaign::where('slug', $slug)
            ->orWhere('custom_slug', $slug)
            ->firstOrFail();

        $referralCode = $request->input('ref')
            ?: session('campaign_referral.' . $campaign->id);
        $fundraiserId = Campaign_Fundraiser::where('campaign_id', $campaign->id)
            ->where('referral_code', $referralCode)
            ->where('status', 'active')
            ->value('id');

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
        $channel = PaymentChannel::with('gateway')->findOrFail($channelId);

        if (!$channel->is_active || !$channel->gateway?->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran yang dipilih sedang tidak tersedia.',
            ], 422);
        }

        // Tentukan nama donatur
        $isAnonim = $request->boolean('is_anonim', false);
        $namaDonatur = $isAnonim ? 'Hamba Allah' : ($request->nama_donatur ?: (auth()->user()?->name ?? 'Hamba Allah'));
        $nominal = (int) $request->nominal;

        // ============================================================
        // 3. BUAT DATA DONASI & PEMBAYARAN
        // ============================================================
        DB::beginTransaction();

        try {
            $donasi = Donasi::create([
                'campaign_id' => $campaign->id,
                'fundraiser_id' => $fundraiserId,
                'user_id' => auth()->id(),
                'nama_donatur' => $namaDonatur,
                'email' => auth()->user()?->email ?? null,
                'no_hp' => $request->no_hp,
                'nominal' => $nominal,
                'pesan_doa' => $request->pesan,
                'is_anonim' => $isAnonim,
            ]);

            $orderId = $this->generateOrderId($donasi);

            $pembayaran = Pembayaran::create([
                'donasi_id' => $donasi->id,
                'payment_channel_id' => $channel->id,
                'order_id' => $orderId,
                'payment_type' => $channel->payment_type ?? 'instant',
                'transaction_status' => 'pending',
            ]);

            // ============================================================
            // 4. ROUTING DINAMIS VIA DRIVER PATTERN
            // ============================================================
            $driver = $this->gatewayManager->driver($channel->gateway);
            $result = $driver->createTransaction($donasi, $pembayaran, $channel);

            DB::commit();

            return response()->json(array_merge(['success' => true], $result));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Donasi store error', [
                'message' => $e->getMessage(),
                'channel_id' => $channelId,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Halaman instruksi pembayaran (VA / Transfer Manual)
     */
    public function instruksi(Pembayaran $pembayaran)
    {
        $pembayaran->load(['donasi.campaign', 'paymentChannel.gateway']);

        $donasi = $pembayaran->donasi;

        if (!$donasi) {
            abort(404, 'Data donasi tidak ditemukan.');
        }

        return view('pages.donasi-instruksi', compact('pembayaran', 'donasi'));
    }

    /**
     * Upload bukti transfer donasi manual oleh donatur
     */
    public function uploadBukti(Request $request, Pembayaran $pembayaran)
    {
        $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'bukti_transfer.required' => 'Pilih file bukti transfer terlebih dahulu.',
            'bukti_transfer.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'bukti_transfer.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $this->manualTransferService->saveBuktiTransfer($pembayaran, $request->file('bukti_transfer'));

            return back()->with('success', 'Bukti transfer berhasil diunggah. Tim OrangBaik akan memverifikasi dalam 1x24 jam.');

        } catch (\Exception $e) {
            Log::error('Upload bukti transfer gagal', [
                'pembayaran_id' => $pembayaran->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengunggah bukti transfer: ' . $e->getMessage());
        }
    }

    /**
     * Halaman status pembayaran (sukses, pending, gagal)
     */
    public function status($status)
    {
        $statusLabels = [
            'sukses' => ['title' => 'Pembayaran Berhasil', 'icon' => 'bi-check-circle-fill', 'color' => 'text-green'],
            'pending' => ['title' => 'Pembayaran Menunggu', 'icon' => 'bi-hourglass-split', 'color' => 'text-orange'],
            'gagal' => ['title' => 'Pembayaran Gagal', 'icon' => 'bi-x-circle-fill', 'color' => 'text-red'],
        ];

        $info = $statusLabels[$status] ?? $statusLabels['pending'];

        return view('pages.donasi-status', compact('status', 'info'));
    }

    /**
     * Generate Order ID unik untuk donasi
     * Format: OB-YYYYMMDD-DONASI_ID-RANDOM
     */
    protected function generateOrderId(Donasi $donasi): string
    {
        $prefix = config('payment.order_id_prefix', 'OB');
        $date = now()->format('Ymd');
        $rand = strtoupper(substr(md5(uniqid()), 0, 4));

        return sprintf('%s-%s-%d-%s', $prefix, $date, $donasi->id, $rand);
    }
}