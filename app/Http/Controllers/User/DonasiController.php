<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Services\MidtransService;
use App\Http\Requests\DonasiRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DonasiController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Menampilkan halaman form donasi
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
            ->filter(function ($donasi) {
                return $donasi->pembayaran
                    && $donasi->pembayaran->transaction_status === 'settlement';
            })
            ->sum('nominal');

        $jumlahDonatur = $campaign->donasi
            ->filter(function ($donasi) {
                return $donasi->pembayaran
                    && $donasi->pembayaran->transaction_status === 'settlement';
            })
            ->count();

        return view('pages.donasi-bayar', compact(
            'campaign',
            'totalTerkumpul',
            'jumlahDonatur'
        ));
    }

    /**
     * Proses donasi dan kembalikan Snap Token (JSON)
     */
    public function store(DonasiRequest $request, $slug)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        // Ambil minimal donasi dari campaign (default 5000)
        $minimalDonasi = $campaign->minimal_donasi ?? 5000;

        // Tentukan nominal
        $nominal = $request->filled('nominal_lainnya')
            ? $request->nominal_lainnya
            : $request->nominal;

        if (empty($nominal) || $nominal < $minimalDonasi) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal donasi Rp ' . number_format($minimalDonasi, 0, ',', '.'),
                'errors' => ['nominal' => ['Minimal donasi Rp ' . number_format($minimalDonasi, 0, ',', '.')]]
            ], 422);
        }

        // Cek anonim
        $isAnonim = $request->has('anonymous_donor') || $request->has('anonymous_message');

        $namaDonatur = $request->nama_donatur ?? 'Orang Baik';
        if ($isAnonim) {
            $namaDonatur = 'Orang Baik';
        }

        $dataDonasi = [
            'campaign_id' => $campaign->id,
            'user_id' => auth()->id(),
            'nama_donatur' => $namaDonatur,
            'email' => auth()->user()->email ?? null,
            'no_hp' => $request->no_hp,
            'nominal' => $nominal,
            'pesan_doa' => $request->pesan,
            'is_anonim' => $isAnonim,
        ];

        $donasi = Donasi::create($dataDonasi);

        $orderId = 'ORDER-' . strtoupper(Str::random(8)) . '-' . $donasi->id;

        $pembayaran = Pembayaran::create([
            'donasi_id' => $donasi->id,
            'order_id' => $orderId,
            'transaction_status' => 'pending',
        ]);

        try {
            $snapToken = $this->midtransService->createTransaction($donasi, $pembayaran);
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran. Silakan coba lagi.'
            ], 500);
        }

        $pembayaran->update(['snap_token' => $snapToken]);

        return response()->json([
            'success' => true,
            'snap_token' => $snapToken,
            'donasi_id' => $donasi->id,
        ]);
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
}