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

        // ================================================
        // SEMUA CAMPAIGN AKTIF (belum berakhir)
        // ================================================
        $campaigns = Campaign::with(['penggalangDana', 'donasi'])
            ->where('is_active', true)
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_berakhir', '>=', $now)
            ->latest()
            ->get();

        if ($request->filled('kategori')) {
            $campaigns = $campaigns->where('kategori_id', $request->kategori);
        }

        // ================================================
        // DARURAT (emergency + approved + aktif)
        // ================================================
        $campaignDarurat = Campaign::with(['penggalangDana', 'donasi'])
            ->emergency()
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_berakhir', '>=', $now)
            ->latest()
            ->get();

        // ================================================
        // BERKELANJUTAN (sustainable + approved + aktif)
        // ================================================
        $campaignBerkelanjutan = Campaign::with(['penggalangDana', 'donasi'])
            ->sustainable()
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_berakhir', '>=', $now)
            ->latest()
            ->get();

        // ================================================
        // TERBARU (hanya 2, aktif)
        // ================================================
        $campaignTerbaru = Campaign::with(['penggalangDana'])
            ->where('is_active', true)
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_berakhir', '>=', $now)
            ->latest()
            ->take(2)
            ->get();

        return view('pages.home', compact(
            'testimoni',
            'kategori',
            'berita',
            'campaigns',
            'campaignDarurat',
            'campaignBerkelanjutan',
            'campaignTerbaru'
        ));
    }
}