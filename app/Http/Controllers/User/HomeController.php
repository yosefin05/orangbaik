<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Kategori;
use App\Models\Testimoni;
use App\Models\Berita;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $testimoni = Testimoni::inRandomOrder()->take(5)->get();
        $kategori = Kategori::all();
        $berita = Berita::all();

        $now = Carbon::now();

        // tampilkan campaign aktif
        $query = Campaign::with(['penggalangDana', 'donasi.pembayaran', 'kategori', 'filter'])
            ->where('is_active', true)
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_berakhir', '>=', $now);

        // Hanya tampilkan campaign yang approved atau regular
        $query->where(function ($q) {
            $q->where('campaign_type', 'regular')
                ->orWhere('approval_status', 'approved')
                ->orWhereNull('approval_status');
        });

        $heroCampaigns = Campaign::with(['penggalangDana', 'donasi.pembayaran'])
        ->where('is_active', true)
        ->where('campaign_type', 'emergency')
        ->where('approval_status', 'approved')
        ->where('tanggal_mulai', '<=', $now)
        ->where('tanggal_berakhir', '>=', $now)
        ->latest()
        ->take(5)
        ->get();

        // DARURAT (emergency + approved + aktif)
        $darurat = (clone $query)
            ->where('campaign_type', 'emergency')
            ->where('approval_status', 'approved')
            ->latest()
            ->take(8)
            ->get();

        // PEMBERDAYAAN (sustainable + approved + aktif)
        $pemberdayaan = (clone $query)
            ->where('campaign_type', 'sustainable')
            ->where('approval_status', 'approved')
            ->latest()
            ->take(8)
            ->get();

        // CAMPAIGN TERBARU (2 item untuk grid kecil)
        $campaignTerbaru = (clone $query)
            ->latest()
            ->take(2)
            ->get();

        $campaigns = (clone $query)
            ->where('campaign_type', 'regular')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($request->filled('kategori')) {
            $campaigns = $campaigns->where('kategori_id', $request->kategori);
        }

        return view('pages.home', compact(
            'testimoni',
            'kategori',
            'berita',
            'campaigns',
            'heroCampaigns',
            'darurat',
            'pemberdayaan',
            'campaignTerbaru',
            'campaigns'
        ));
    }
}