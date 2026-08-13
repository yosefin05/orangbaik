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
        return view(
            'pages.penggalang_dana.create_organisasi'
        );
    }

    public function storeOrganisasi(Request $request)
    {
        $request->validate([
            'thumbnail' => 'required|image|max:2048',
            'foto_profil' => 'required|image|max:2048',
            'nama_penggalang' => 'required|max:255',
            'tahun_berdiri' => 'required|digits:4',
            'alamat' => 'required',
            'deskripsi' => 'required',
            'visi' => 'required',
            'misi' => 'required',
            'email' => 'required|email',
            'no_telepon' => 'required',
            'nama_dokumen.0' => 'required',
            'file_dokumen.0' => 'required|url',
        ]);

        DB::beginTransaction();

        try {

            $thumbnail = $request
                ->file('thumbnail')
                ->store(
                    'penggalang_dana/thumbnail',
                    'public'
                );

            $fotoProfil = $request
                ->file('foto_profil')
                ->store(
                    'penggalang_dana/profil',
                    'public'
                );

            $penggalangDana = Penggalang_Dana::create([
                'user_id' => auth()->id(),
                'jenis_penggalang' =>
                    $request->jenis_penggalang,
                'thumbnail' =>
                    $thumbnail,
                'foto_profil' =>
                    $fotoProfil,
                'nama_penggalang' =>
                    $request->nama_penggalang,
                'tahun_berdiri' =>
                    $request->tahun_berdiri,
                'alamat' =>
                    $request->alamat,
                'deskripsi' =>
                    $request->deskripsi,
                'visi' =>
                    $request->visi,
                'misi' =>
                    $request->misi,
                'email' =>
                    $request->email,
                'no_telepon' =>
                    $request->no_telepon,
                'instagram' =>
                    $request->instagram,
                'facebook' =>
                    $request->facebook,
                'youtube' =>
                    $request->youtube,
                'tiktok' =>
                    $request->tiktok,
                'status' =>
                    'pending',
            ]);

            foreach ($request->nama_dokumen as $index => $namaDokumen) {

                if (
                    empty($namaDokumen) ||
                    empty($request->file_dokumen[$index])
                ) {
                    continue;
                }

                Penggalang_Dana_Dokumen::create([
                    'penggalang_dana_id' =>
                        $penggalangDana->id,
                    'nama_dokumen' =>
                        $namaDokumen,
                    'file_dokumen' =>
                        $request->file_dokumen[$index],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('profile.user')
                ->with(
                    'success',
                    'Pengajuan penggalang dana berhasil dikirim.'
                );

        } catch (\Exception $e) {

            DB::rollBack();
            dd($e->getMessage());
        }

    }

    public function storeIndividu(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|max:2048',
            'nama_penggalang' => 'required|max:255',
            'alamat' => 'required',
            'deskripsi' => 'nullable', 
            'email' => 'required|email',
            'no_telepon' => 'required',
            'nama_dokumen.0' => 'required|string',
            'file_dokumen.0' => 'required|url',
            'visi' => 'nullable',
            'misi' => 'nullable',
            'instagram' => 'nullable',
            'facebook' => 'nullable',
            'youtube' => 'nullable',
            'tiktok' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            $fotoProfil = $request->file('foto_profil')
                ->store('penggalang_dana/profil', 'public');

            $penggalangDana = Penggalang_Dana::create([
                'user_id' => auth()->id(),
                'jenis_penggalang' => 'individu',
                'thumbnail' => null,
                'foto_profil' => $fotoProfil,
                'nama_penggalang' => $request->nama_penggalang,
                'tahun_berdiri' => null,
                'alamat' => $request->alamat,
                'deskripsi' => $request->deskripsi,
                'visi' => $request->visi,
                'misi' => $request->misi,
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'instagram' => $request->instagram,
                'facebook' => $request->facebook,
                'youtube' => $request->youtube,
                'tiktok' => $request->tiktok,
                'status' => 'pending',
            ]);

            foreach ($request->nama_dokumen as $i => $nama) {
                if (empty($nama) || empty($request->file_dokumen[$i])) {
                    continue;
                }
                Penggalang_Dana_Dokumen::create([
                    'penggalang_dana_id' => $penggalangDana->id,
                    'nama_dokumen' => $nama,
                    'file_dokumen' => $request->file_dokumen[$i],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('profile.user')
                ->with('success', 'Pengajuan berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function profile()
    {
        $penggalang = auth()->user()
            ->penggalangDana()
            ->with([
                'penggalangDanaDokumen',
                'campaign'
            ])
            ->firstOrFail();

        if (!$penggalang->status_read) {
            $penggalang->update([
                'status_read' => true
            ]);
        }

        $isOwner = auth()->check() &&
            optional(auth()->user()->penggalangDana)->id === $penggalang->id;

        return view(
            'pages.profil-penggalang',
            compact('penggalang', 'isOwner')
        );
    }

    public function rejected()
    {
        $penggalang = auth()->user()
            ->penggalangDana()
            ->with('penggalangDanaDokumen')
            ->firstOrFail();

        // tandai notifikasi sudah dibaca
        if (!$penggalang->status_read) {
            $penggalang->update([
                'status_read' => true
            ]);
        }

        return view(
            'pages.penggalang_dana.rejected',
            compact('penggalang')
        );
    }

    public function edit($id)
    {
        $penggalang = Auth::user()
            ->penggalangDana()
            ->with('penggalangDanaDokumen')
            ->first();

        if ($penggalang->jenis_penggalang == 'organisasi') {
            return view('pages.penggalang_dana.edit_organisasi', compact('penggalang'));
        }

        return view('pages.penggalang_dana.edit_individu', compact('penggalang'));
    }

    public function update(Request $request, $id)
    {
        $penggalang = Penggalang_Dana::findOrFail($id);

        // Validasi dasar
        $validated = $request->validate([
            'nama_penggalang' => 'required|string',
            'email' => 'required|email',
            'no_telepon' => 'required',
            'alamat' => 'required',
            'deskripsi' => 'required',
            'visi' => 'required',
            'misi' => 'required',
            'instagram' => 'nullable',
            'facebook' => 'nullable',
            'youtube' => 'nullable',
            'tiktok' => 'nullable',
            'foto_profil' => 'nullable|image',
            'thumbnail' => 'nullable|image',
            'tahun_berdiri' => 'nullable',
            'nama_dokumen' => 'nullable|array',
            'file_dokumen' => 'nullable|array',
        ]);

        // === AMBIL DATA DOKUMEN LAMA ===
        $oldDokumen = $penggalang->penggalangDanaDokumen()->get()->keyBy('id');

        // === CEK PERUBAHAN DOKUMEN ===
        $isDokumenChanged = false;

        // Ambil input dokumen baru (array)
        $newNama = $request->input('nama_dokumen', []);
        $newFile = $request->input('file_dokumen', []);

        // Bandingkan jumlah & isi
        if (count($oldDokumen) !== count($newNama)) {
            $isDokumenChanged = true;
        } else {
            foreach ($oldDokumen as $index => $dok) {
                // Bandingkan nama dan file
                $oldNama = $dok->nama_dokumen;
                $oldFile = $dok->file_dokumen;
                $newNamaVal = $newNama[$index] ?? '';
                $newFileVal = $newFile[$index] ?? '';

                if ($oldNama !== $newNamaVal || $oldFile !== $newFileVal) {
                    $isDokumenChanged = true;
                    break;
                }
            }
        }

        // === UPLOAD FOTO PROFIL ===
        if ($request->hasFile('foto_profil')) {
            if ($penggalang->foto_profil && Storage::disk('public')->exists($penggalang->foto_profil)) {
                Storage::disk('public')->delete($penggalang->foto_profil);
            }
            $validated['foto_profil'] = $request->file('foto_profil')->store('penggalang_dana/profil', 'public');
        }

        // === UPLOAD THUMBNAIL ===
        if ($request->hasFile('thumbnail')) {
            if ($penggalang->thumbnail && Storage::disk('public')->exists($penggalang->thumbnail)) {
                Storage::disk('public')->delete($penggalang->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('penggalang_dana/thumbnail', 'public');
        }

        // === UPDATE STATUS BERDASARKAN PERUBAHAN DOKUMEN ===
        // Jika ada perubahan dokumen, set ke pending (kecuali sedang pending, tetap pending)
        if ($isDokumenChanged) {
            $validated['status'] = 'pending';
            $validated['status_read'] = false;
            // Reset catatan verifikasi jika perlu
            $validated['catatan_verifikasi'] = null;
            $validated['verified_by'] = null;
            $validated['verified_at'] = null;
            if (in_array($penggalang->status, ['rejected', 'approved'])) {
                $validated['revision_count'] = $penggalang->revision_count + 1;
            }
        } else {
        
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
                    'nama_dokumen' => $nama,
                    'file_dokumen' => $request->file_dokumen[$i],
                ]);
            }
        }

        // Pesan sukses dengan informasi status
        $message = 'Data berhasil diperbarui.';
        if ($isDokumenChanged) {
            $message .= ' Dokumen berubah, status kini PENDING dan menunggu verifikasi ulang.';
        }

        return redirect()
            ->route('profil.penggalang', $penggalang->id)
            ->with('success', $message);
    }

    public function resubmit($id)
    {
        $penggalang = Penggalang_Dana::findOrFail($id);

        // Hanya bisa di-resubmit jika status rejected
        if ($penggalang->status !== 'rejected') {
            return redirect()->back()->with('error', 'Pengajuan ini tidak dapat diajukan ulang.');
        }

        // Ubah status ke pending, reset data verifikasi
        $penggalang->status = 'pending';
        $penggalang->status_read = false;
        $penggalang->catatan_verifikasi = null;
        $penggalang->verified_by = null;
        $penggalang->verified_at = null;
        $penggalang->revision_count = $penggalang->revision_count + 1;
        $penggalang->save();

        return redirect()
            ->route('profile.user')
            ->with('success', 'Pengajuan ulang berhasil! Status kini PENDING, menunggu verifikasi admin.');
    }


}