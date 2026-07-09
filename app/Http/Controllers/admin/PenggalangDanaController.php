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

    public function approve(Penggalang_Dana $penggalangDana)
    {
        $penggalangDana->update([

            'status' => 'approved',
            'verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'catatan_verifikasi' => 'Selamat! Pengajuan Anda telah disetujui. Sekarang Anda dapat mulai membuat campaign penggalangan dana.',
            'status_read' => false,

        ]);

        return back()->with(
            'success',
            'Penggalang dana berhasil disetujui.'
        );
    }

    public function reject(
        Penggalang_Dana $penggalangDana,
        Request $request
    ) {
        $penggalangDana->update([
            'status' => 'rejected',
            'verified' => false,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'catatan_verifikasi' => $request->catatan_verifikasi,
            'status_read' => false

        ]);

        return back()->with(
            'success',
            'Penggalang dana berhasil ditolak.'
        );
    }
    public function destroy(
        Penggalang_Dana $penggalangDana
    ) {
        DB::transaction(function () use ($penggalangDana) {
            // Hapus dokumen terkait
            $penggalangDana->penggalangDanaDokumen()->delete();

            // Hapus penggalang dana
            $penggalangDana->delete();
        });

        return back()->with(
            'success',
            'Penggalang dana berhasil dihapus.'
        );
    }

    public function verify(Penggalang_Dana $penggalangDana)
    {
        $penggalangDana->update([
            'verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Organisasi berhasil diverifikasi.');
    }

    public function unverify(Penggalang_Dana $penggalangDana)
    {
        $penggalangDana->update([
            'verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return back()->with('success', 'Verifikasi dicabut.');
    }
}