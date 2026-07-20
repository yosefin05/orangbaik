<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Kategori;
use App\Models\Testimoni;
use App\Models\Berita;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $testimoni = Testimoni::inRandomOrder()->take(5)->get();

        $kategori = Kategori::all();

        $berita = Berita::all();

        $campaigns = Campaign::with([
            'penggalangDana',
            'donasi'
        ])
            ->where('is_active', true);

        if ($request->filled('kategori')) {
            $campaigns->where('kategori_id', $request->kategori);
        }

        $campaigns = Campaign::with(['penggalangDana', 'donasi'])
            ->regular()
            ->latest()
            ->get();

        $campaignDarurat = Campaign::with(['penggalangDana', 'donasi'])
            ->emergency()
            ->latest()
            ->get();

        $campaignBerkelanjutan = Campaign::with(['penggalangDana', 'donasi'])
            ->sustainable()
            ->latest()
            ->get();

        $campaignTerbaru = Campaign::with(['penggalangDana'])
            ->latest()
            ->take(4)
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
