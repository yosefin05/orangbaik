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
            ->with(['packages', 'penggalangDana', 'donasi'])
            ->firstOrFail();

        $totalTerkumpul = $campaign->donasi->sum('nominal');
        $jumlahDonatur = $campaign->donasi->count();

        return view('pages.donasi-bayar', compact(
            'campaign',
            'totalTerkumpul',
            'jumlahDonatur',
        ));
    }

    /**
     * Proses donasi dan redirect ke halaman konfirmasi
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

        // ... sisanya sama
    }

    /**
     * Halaman konfirmasi pembayaran (Snap popup)
     */
    public function confirm(Donasi $donasi)
    {
        $pembayaran = $donasi->pembayaran;
        $snapToken = session('snap_token') ?? $pembayaran->snap_token;

        if (!$snapToken) {
            abort(404, 'Token pembayaran tidak ditemukan.');
        }

        return view('pages.donasi-konfirmasi', compact('donasi', 'snapToken'));
    }

    /**
     * Halaman status donasi
     */
    public function status($status)
    {
        $allowed = ['sukses', 'pending', 'gagal'];
        if (!in_array($status, $allowed)) {
            abort(404);
        }

        // Jika status pending, pastikan ada snap_token di session
        if ($status == 'pending') {
            $snapToken = session('snap_token');
            $donasiId = session('donasi_id');

            if (!$snapToken) {
                return redirect()->route('home')->with('error', 'Token pembayaran tidak ditemukan.');
            }
        }

        return view('pages.donasi-status', compact('status'));
    }
}