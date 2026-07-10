<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Berita;
use App\Models\Komentar;

class KomentarController extends Controller
{
    public function store(Request $request, Berita $berita)
    {
        $request->validate([
            'komentar' => 'required'
        ]);

        Komentar::create([
            'komentar' => $request->komentar,
            'user_id' => auth()->id(),
            'berita_id' => $berita->id,
        ]);

        return back();
    }
}
