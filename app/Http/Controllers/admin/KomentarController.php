<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Komentar;

class KomentarController extends Controller
{
    public function index()
    {
        $komentar = Komentar::with(['user', 'berita'])
            ->latest()
            ->paginate(10);

        return view('admin.komentar.index', compact('komentar'));
    }

    public function show(Komentar $komentar)
    {
        return redirect()->route(
            'berita.show',
            $komentar->berita->slug
        ) . 'komentar-' . $komentar->id;
    }

    public function destroy(Komentar $komentar)
    {
        $komentar->delete();

        return redirect()
            ->route('admin.komentar.index')
            ->with(
                'success',
                'Komentar berhasil dihapus.'
            );
    }
}
