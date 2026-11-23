<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenggalangDanaController extends Controller
{
    public function index(Request $request)
    {
        $query = Penggalang_Dana::with('user');

        // Filter kategori (organisasi, yayasan_lembaga, komunitas, individu)
        if ($request->filled('jenis_penggalang')) {
            $query->where('jenis_penggalang', $request->jenis_penggalang);
        }

        // Filter status verifikasi (pending, approved, rejected)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian nama, email, nomor
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_penggalang', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('no_telepon', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        $penggalangDana = $query->latest()->paginate(10)->withQueryString();
        $categories = Penggalang_Dana::getCategoryLabels();
        $statuses = ['pending', 'approved', 'rejected'];

        return view(
            'admin.penggalang_dana.index',
            compact('penggalangDana', 'categories', 'statuses')
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
        if ($penggalangDana->status === 'approved') {
            return back()->with('warning', 'Penggalang dana ini sudah disetujui.');
        }

        $approvalData = [

            'status' => 'approved',
            'verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'catatan_verifikasi' => 'Selamat! Pengajuan Anda telah disetujui. Sekarang Anda dapat mulai membuat campaign penggalangan dana.',
            'status_read' => false,

        ];
        if (is_array($penggalangDana->pending_changes)) {
            $approvalData = array_merge($approvalData, $penggalangDana->pending_changes, ['pending_changes' => null]);
        }
        $penggalangDana->update($approvalData);

        return back()->with(
            'success',
            'Penggalang dana berhasil disetujui.'
        );
    }

    public function reject(
        Penggalang_Dana $penggalangDana,
        Request $request
    ) {
        $request->validate([
            'catatan_verifikasi' => 'required|string|max:1000',
        ]);

        $penggalangDana->update([
            'status' => 'rejected',
            'verified' => false,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'catatan_verifikasi' => $request->catatan_verifikasi,
            'status_read' => false,
            'pending_changes' => null,

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

    public function toggleDokumenPublic(Penggalang_Dana_Dokumen $dokumen)
    {
        // Dokumen penggalang individu dilarang keras dipublikasikan (KTP/identitas pribadi)
        if ($dokumen->penggalangDana && $dokumen->penggalangDana->jenis_penggalang === 'individu') {
            return back()->with('error', 'Dokumen identitas penggalang individu tidak boleh dipublikasikan demi perlindungan data pribadi.');
        }

        $dokumen->update([
            'is_public' => !$dokumen->is_public,
        ]);

        $status = $dokumen->is_public ? 'publik' : 'privat';
        return back()->with('success', "Visibilitas dokumen berhasil diubah menjadi {$status}.");
    }
}
