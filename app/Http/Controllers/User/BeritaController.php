<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Berita;

class BeritaController extends Controller
{
    public function index()
    {
        $beritas = Berita::with(['user', 'gambar'])
            ->latest()
            ->paginate(12);

        return view('pages.berita', compact('beritas'));
    }
    public function show($slug)
    {
        $berita = Berita::with([
            'user',
            'gambar',
            'komentar.user'
        ])->where('slug', $slug)
          ->firstOrFail();

        $relatedNews = Berita::where('id', '!=', $berita->id)
            ->latest()
            ->take(8)
            ->get();

        return view('pages.detail-berita', compact(
            'berita',
            'relatedNews'
        ));
    }
}
