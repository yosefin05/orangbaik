<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Berita;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('q');

        $campaigns = collect();
        $berita = collect();

        if ($keyword) {
            // Cari campaign
            $campaigns = Campaign::with(['penggalangDana', 'donasi', 'kategori'])
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('campaign_type', 'regular')
                        ->orWhere('approval_status', 'approved');
                })
                ->where('judul', 'LIKE', '%' . $keyword . '%')
                ->latest()
                ->get();

            // Cari berita
            $berita = Berita::where('judul', 'LIKE', '%' . $keyword . '%')
                ->latest()
                ->get();
        }

        return view('pages.search', compact('keyword', 'campaigns', 'berita'));
    }
}