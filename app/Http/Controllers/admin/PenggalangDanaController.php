<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenggalangDanaController extends Controller
{
    public function index()
    {
        $penggalangDana = Penggalang_Dana::with('user')
            ->latest()
            ->paginate(10);

        return view(
            'admin.penggalang_dana.index',
            compact('penggalangDana')
        );
    }

    public function show(
        Penggalang_Dana $penggalangDana
    ) {
        $penggalangDana->load([
            'user',
            'penggalangDanaDokumen',
            'verifier'
        ]);

        return view(
            'admin.penggalang_dana.show',
            compact('penggalangDana')
        );
    }

    public function approve(
        Penggalang_Dana $penggalangDana
    ) {
        $penggalangDana->update([
            'status' => 'approved',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with(
            'success',
            'Penggalang dana berhasil disetujui.'
        );
    }

    public function reject(
        Penggalang_Dana $penggalangDana
    ) {
        $penggalangDana->update([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with(
            'success',
            'Penggalang dana berhasil ditolak.'
        );
    }
}