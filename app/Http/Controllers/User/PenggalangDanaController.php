<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PenggalangDanaController extends Controller
{
    public function createOrganisasi()
    {
        return view('pages.penggalang_dana.create_organisasi');
    }

    public function storeOrganisasi(Request $request)
    {
        $request->validate([
            'thumbnail'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'foto_profil'      => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'nama_penggalang'  => 'required|max:255',
            'tahun_berdiri'    => 'required|digits:4',
            'alamat'           => 'required',
            'deskripsi'        => 'required',
            'visi'             => 'required',
            'misi'             => 'required',
            'email'            => 'required|email',
            'no_telepon'       => 'required',
            'nama_dokumen.0'   => 'required',
            'file_dokumen.0'   => 'required|url',
        ]);

        DB::beginTransaction();

        try {
            $thumbnail = $request->file('thumbnail')
                ->store('penggalang_dana/thumbnail', 'public');

            $fotoProfil = $request->file('foto_profil')
                ->store('penggalang_dana/profil', 'public');

            $penggalangDana = Penggalang_Dana::create([
                'user_id'          => auth()->id(),
                'jenis_penggalang' => $request->jenis_penggalang,
                'thumbnail'        => $thumbnail,
                'foto_profil'      => $fotoProfil,
                'nama_penggalang'  => $request->nama_penggalang,
                'tahun_berdiri'    => $request->tahun_berdiri,
                'alamat'           => $request->alamat,
                'deskripsi'        => $request->deskripsi,
                'visi'             => $request->visi,
                'misi'             => $request->misi,
                'email'            => $request->email,
                'no_telepon'       => $request->no_telepon,
                'instagram'        => $request->instagram,
                'facebook'         => $request->facebook,
                'youtube'          => $request->youtube,
                'tiktok'           => $request->tiktok,
                'status'           => 'pending',
            ]);

            foreach ($request->nama_dokumen as $index => $namaDokumen) {
                if (empty($namaDokumen) || empty($request->file_dokumen[$index])) {
                    continue;
                }

                Penggalang_Dana_Dokumen::create([
                    'penggalang_dana_id' => $penggalangDana->id,
                    'nama_dokumen'       => $namaDokumen,
                    'file_dokumen'       => $request->file_dokumen[$index],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('profile.user')
                ->with('success', 'Pengajuan penggalang dana berhasil dikirim. Tim admin akan memverifikasi dalam 1-3 hari kerja.');

        } catch (\Exception $e) {
            DB::rollBack();
            // PERBAIKAN: hapus dd(), ganti dengan proper error redirect
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function storeIndividu(Request $request)
    {
        $request->validate([
            'foto_profil'     => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'nama_penggalang' => 'required|max:255',
            'alamat'          => 'required',
            'deskripsi'       => 'nullable',
            'email'           => 'required|email',
            'no_telepon'      => 'required',
            'nama_dokumen.0'  => 'required|string',
            'file_dokumen.0'  => 'required|url',
            'visi'            => 'nullable',
            'misi'            => 'nullable',
            'instagram'       => 'nullable',
            'facebook'        => 'nullable',
            'youtube'         => 'nullable',
            'tiktok'          => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            $fotoProfil = $request->file('foto_profil')
                ->store('penggalang_dana/profil', 'public');

            $penggalangDana = Penggalang_Dana::create([
                'user_id'          => auth()->id(),
                'jenis_penggalang' => 'individu',
                'thumbnail'        => null,
                'foto_profil'      => $fotoProfil,
                'nama_penggalang'  => $request->nama_penggalang,
                'tahun_berdiri'    => null,
                'alamat'           => $request->alamat,
                'deskripsi'        => $request->deskripsi,
                'visi'             => $request->visi,
                'misi'             => $request->misi,
                'email'            => $request->email,
                'no_telepon'       => $request->no_telepon,
                'instagram'        => $request->instagram,
                'facebook'         => $request->facebook,
                'youtube'          => $request->youtube,
                'tiktok'           => $request->tiktok,
                'status'           => 'pending',
            ]);

            foreach ($request->nama_dokumen as $i => $nama) {
                if (empty($nama) || empty($request->file_dokumen[$i])) {
                    continue;
                }
                Penggalang_Dana_Dokumen::create([
                    'penggalang_dana_id' => $penggalangDana->id,
                    'nama_dokumen'       => $nama,
                    'file_dokumen'       => $request->file_dokumen[$i],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('profile.user')
                ->with('success', 'Pengajuan berhasil dikirim. Tim admin akan memverifikasi dalam 1-3 hari kerja.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: Gagal menyimpan data.');
        }
    }

    /**
     * PUBLIC profile penggalang berdasarkan {id} param.
     * Guest dapat membuka. Owner detection terpisah dari pengambilan data.
     */
    public function profile($id)
    {
        // FIX: Gunakan ID dari param, BUKAN dari auth()->user()
        $penggalang = Penggalang_Dana::with([
            'penggalangDanaDokumen',
            'campaign',
            'user',
        ])->findOrFail($id);

        // Owner detection: cek apakah user yang sedang login adalah pemilik profil ini
        $isOwner = auth()->check() && auth()->user()->id === $penggalang->user_id;

        // Jika owner, tandai notifikasi sudah dibaca
        if ($isOwner && !$penggalang->status_read) {
            $penggalang->update(['status_read' => true]);
        }

        return view('pages.profil-penggalang', compact('penggalang', 'isOwner'));
    }

    public function rejected()
    {
        $penggalang = auth()->user()
            ->penggalangDana()
            ->with('penggalangDanaDokumen')
            ->firstOrFail();

        if (!$penggalang->status_read) {
            $penggalang->update(['status_read' => true]);
        }

        return view('pages.penggalang_dana.rejected', compact('penggalang'));
    }

    /**
     * Edit form penggalang dana. Hanya owner yang boleh akses.
     */
    public function edit($id)
    {
        // Ambil penggalang berdasarkan ID
        $penggalang = Penggalang_Dana::with('penggalangDanaDokumen')->findOrFail($id);

        // FIX: Authorization — hanya owner yang boleh edit
        if ($penggalang->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit profil ini.');
        }

        if ($penggalang->jenis_penggalang === 'organisasi') {
            return view('pages.penggalang_dana.edit_organisasi', compact('penggalang'));
        }

        return view('pages.penggalang_dana.edit_individu', compact('penggalang'));
    }

    /**
     * Update penggalang dana. Hanya owner yang boleh update.
     */
    public function update(Request $request, $id)
    {
        $penggalang = Penggalang_Dana::findOrFail($id);

        // FIX: Authorization — hanya owner yang boleh update
        if ($penggalang->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah profil ini.');
        }

        $validated = $request->validate([
            'nama_penggalang' => 'required|string',
            'email'           => 'required|email',
            'no_telepon'      => 'required',
            'alamat'          => 'required',
            'deskripsi'       => 'required',
            'visi'            => 'required',
            'misi'            => 'required',
            'instagram'       => 'nullable',
            'facebook'        => 'nullable',
            'youtube'         => 'nullable',
            'tiktok'          => 'nullable',
            'foto_profil'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'thumbnail'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tahun_berdiri'   => 'nullable',
            'nama_dokumen'    => 'nullable|array',
            'file_dokumen'    => 'nullable|array',
        ]);

        // Ambil data dokumen lama
        $oldDokumen = $penggalang->penggalangDanaDokumen()->get()->keyBy('id');

        // Cek perubahan dokumen
        $isDokumenChanged = false;
        $newNama = $request->input('nama_dokumen', []);
        $newFile = $request->input('file_dokumen', []);

        if (count($oldDokumen) !== count($newNama)) {
            $isDokumenChanged = true;
        } else {
            foreach ($oldDokumen as $index => $dok) {
                $newNamaVal = $newNama[$index] ?? '';
                $newFileVal = $newFile[$index] ?? '';
                if ($dok->nama_dokumen !== $newNamaVal || $dok->file_dokumen !== $newFileVal) {
                    $isDokumenChanged = true;
                    break;
                }
            }
        }

        // Upload foto profil
        if ($request->hasFile('foto_profil')) {
            if ($penggalang->foto_profil && Storage::disk('public')->exists($penggalang->foto_profil)) {
                Storage::disk('public')->delete($penggalang->foto_profil);
            }
            $validated['foto_profil'] = $request->file('foto_profil')
                ->store('penggalang_dana/profil', 'public');
        }

        // Upload thumbnail
        if ($request->hasFile('thumbnail')) {
            if ($penggalang->thumbnail && Storage::disk('public')->exists($penggalang->thumbnail)) {
                Storage::disk('public')->delete($penggalang->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')
                ->store('penggalang_dana/thumbnail', 'public');
        }

        // Jika dokumen berubah, reset ke pending
        if ($isDokumenChanged) {
            $validated['status']               = 'pending';
            $validated['status_read']          = false;
            $validated['catatan_verifikasi']   = null;
            $validated['verified_by']          = null;
            $validated['verified_at']          = null;
            if (in_array($penggalang->status, ['rejected', 'approved'])) {
                $validated['revision_count'] = $penggalang->revision_count + 1;
            }
        }

        $penggalang->update($validated);

        if ($request->filled('nama_dokumen')) {
            $penggalang->penggalangDanaDokumen()->delete();

            foreach ($request->nama_dokumen as $i => $nama) {
                if (empty($nama) || empty($request->file_dokumen[$i])) {
                    continue;
                }
                Penggalang_Dana_Dokumen::create([
                    'penggalang_dana_id' => $penggalang->id,
                    'nama_dokumen'       => $nama,
                    'file_dokumen'       => $request->file_dokumen[$i],
                ]);
            }
        }

        $message = 'Data berhasil diperbarui.';
        if ($isDokumenChanged) {
            $message .= ' Dokumen berubah, status kini PENDING dan menunggu verifikasi ulang.';
        }

        return redirect()
            ->route('profil.penggalang', $penggalang->id)
            ->with('success', $message);
    }

    /**
     * Resubmit pengajuan yang ditolak. Hanya owner yang boleh.
     */
    public function resubmit($id)
    {
        $penggalang = Penggalang_Dana::findOrFail($id);

        // FIX: Authorization — hanya owner yang boleh resubmit
        if ($penggalang->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengajukan ulang profil ini.');
        }

        if ($penggalang->status !== 'rejected') {
            return redirect()->back()
                ->with('error', 'Pengajuan ini tidak dapat diajukan ulang.');
        }

        $penggalang->status              = 'pending';
        $penggalang->status_read         = false;
        $penggalang->catatan_verifikasi  = null;
        $penggalang->verified_by         = null;
        $penggalang->verified_at         = null;
        $penggalang->revision_count      = $penggalang->revision_count + 1;
        $penggalang->save();

        return redirect()
            ->route('profile.user')
            ->with('success', 'Pengajuan ulang berhasil! Status kini PENDING, menunggu verifikasi admin.');
    }
}