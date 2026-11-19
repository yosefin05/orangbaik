<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenggalangDanaController extends Controller
{
    public function createOrganisasi(Request $request)
    {
        $selectedCategory = $request->query('type', Penggalang_Dana::CATEGORY_ORGANISASI);
        if (!in_array($selectedCategory, [
            Penggalang_Dana::CATEGORY_ORGANISASI,
            Penggalang_Dana::CATEGORY_YAYASAN,
            Penggalang_Dana::CATEGORY_KOMUNITAS,
        ])) {
            $selectedCategory = Penggalang_Dana::CATEGORY_ORGANISASI;
        }

        $categories = [
            Penggalang_Dana::CATEGORY_ORGANISASI => 'Organisasi',
            Penggalang_Dana::CATEGORY_YAYASAN    => 'Yayasan/Lembaga',
            Penggalang_Dana::CATEGORY_KOMUNITAS  => 'Komunitas',
        ];

        return view('pages.penggalang_dana.create_organisasi', compact('categories', 'selectedCategory'));
    }

    public function storeOrganisasi(Request $request)
    {
        $request->validate([
            'jenis_penggalang' => 'nullable|string|in:organisasi,yayasan_lembaga,komunitas',
            'thumbnail'        => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'foto_profil'      => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'nama_penggalang'  => 'required|max:255',
            'tahun_berdiri'    => 'required|digits:4',
            'alamat'           => 'required',
            'deskripsi'        => 'required',
            'visi'             => 'required',
            'misi'             => 'required',
            'email'            => 'required|email',
            'no_telepon'       => 'required',
            'nama_dokumen'     => 'required|array|min:1',
            'nama_dokumen.0'   => 'required|string',
            'dokumen_files.*'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'file_dokumen.*'   => 'nullable|string',
            'file_dokumen.0'   => 'required_without:dokumen_files.0|string',
        ]);

        // Cek setidaknya ada 1 file atau URL dokumen
        $hasDoc0 = $request->hasFile('dokumen_files.0') || !empty($request->input('file_dokumen.0'));
        if (!$hasDoc0) {
            return redirect()->back()->withInput()->withErrors([
                'file_dokumen.0' => 'Dokumen persyaratan pertama wajib diunggah (PDF, JPG, PNG maks 5MB) atau sertakan link.',
            ]);
        }

        $jenis = $request->input('jenis_penggalang', Penggalang_Dana::CATEGORY_ORGANISASI);
        if (!in_array($jenis, [
            Penggalang_Dana::CATEGORY_ORGANISASI,
            Penggalang_Dana::CATEGORY_YAYASAN,
            Penggalang_Dana::CATEGORY_KOMUNITAS,
        ])) {
            $jenis = Penggalang_Dana::CATEGORY_ORGANISASI;
        }

        DB::beginTransaction();

        try {
            $thumbnail = $request->file('thumbnail')
                ->store('penggalang_dana/thumbnail', 'public');

            $fotoProfil = $request->file('foto_profil')
                ->store('penggalang_dana/profil', 'public');

            $penggalangDana = Penggalang_Dana::create([
                'user_id'          => auth()->id(),
                'jenis_penggalang' => $jenis,
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

            $namaDokumenArr = $request->input('nama_dokumen', []);
            foreach ($namaDokumenArr as $index => $namaDokumen) {
                if (empty($namaDokumen)) {
                    continue;
                }

                $filePath = null;
                $disk = 'private';

                // Prioritaskan file upload langsung ke storage private
                if ($request->hasFile("dokumen_files.{$index}")) {
                    $uploadedFile = $request->file("dokumen_files.{$index}");
                    $filePath = $uploadedFile->store("dokumen_penggalang/{$penggalangDana->id}", 'private');
                    $disk = 'private';
                } elseif (!empty($request->file_dokumen[$index])) {
                    $filePath = trim($request->file_dokumen[$index]);
                    $disk = 'private';
                }

                if ($filePath) {
                    Penggalang_Dana_Dokumen::create([
                        'penggalang_dana_id' => $penggalangDana->id,
                        'nama_dokumen'       => $namaDokumen,
                        'file_dokumen'       => $filePath,
                        'is_public'          => false,
                        'storage_disk'       => $disk,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('profile.user')
                ->with('success', 'Pengajuan penggalang dana berhasil dikirim. Tim admin akan memverifikasi dalam 1-3 hari kerja.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function storeIndividu(Request $request)
    {
        $request->validate([
            'foto_profil'     => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'nama_penggalang' => 'required|max:255',
            'alamat'          => 'required',
            'deskripsi'       => 'nullable',
            'email'           => 'required|email',
            'no_telepon'      => 'required',
            'nama_dokumen'    => 'required|array|min:1',
            'nama_dokumen.0'  => 'required|string',
            'dokumen_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'file_dokumen.*'  => 'nullable|string',
            'visi'            => 'nullable',
            'misi'            => 'nullable',
            'instagram'       => 'nullable',
            'facebook'        => 'nullable',
            'youtube'         => 'nullable',
            'tiktok'          => 'nullable',
        ]);

        $hasDoc0 = $request->hasFile('dokumen_files.0') || !empty($request->input('file_dokumen.0'));
        if (!$hasDoc0) {
            return redirect()->back()->withInput()->withErrors([
                'dokumen_files.0' => 'Dokumen identitas KTP wajib diunggah (PDF, JPG, PNG maks 5MB) atau sertakan link.',
            ]);
        }

        DB::beginTransaction();

        try {
            $fotoProfil = $request->file('foto_profil')
                ->store('penggalang_dana/profil', 'public');

            $penggalangDana = Penggalang_Dana::create([
                'user_id'          => auth()->id(),
                'jenis_penggalang' => Penggalang_Dana::CATEGORY_INDIVIDU,
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

            $namaDokumenArr = $request->input('nama_dokumen', []);
            foreach ($namaDokumenArr as $i => $nama) {
                if (empty($nama)) {
                    continue;
                }

                $filePath = null;
                $disk = 'private';

                // Prioritaskan upload file identitas (KTP) ke disk PRIVATE
                if ($request->hasFile("dokumen_files.{$i}")) {
                    $uploadedFile = $request->file("dokumen_files.{$i}");
                    $filePath = $uploadedFile->store("dokumen_penggalang/{$penggalangDana->id}", 'private');
                    $disk = 'private';
                } elseif (!empty($request->file_dokumen[$i])) {
                    $filePath = trim($request->file_dokumen[$i]);
                    $disk = 'private';
                }

                if ($filePath) {
                    Penggalang_Dana_Dokumen::create([
                        'penggalang_dana_id' => $penggalangDana->id,
                        'nama_dokumen'       => $nama,
                        'file_dokumen'       => $filePath,
                        'is_public'          => false, // KTP Individu SELALU PRIVATE
                        'storage_disk'       => $disk,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('profile.user')
                ->with('success', 'Pengajuan berhasil dikirim. Tim admin akan memverifikasi dalam 1-3 hari kerja.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * PUBLIC profile penggalang berdasarkan {id} atau {slug}.
     */
    public function profile($id)
    {
        $penggalang = Penggalang_Dana::with([
            'campaign',
            'user' => fn($q) => $q->select('id', 'name'),
        ])->where('id', $id)
          ->orWhere('slug', $id)
          ->firstOrFail();

        $isOwner = auth()->check() && (int) auth()->id() === (int) $penggalang->user_id;
        $isAdmin = auth()->check() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin();

        // Profil yang belum approved (pending/rejected) hanya boleh diakses oleh owner dan admin
        if ($penggalang->status !== 'approved' && !$isOwner && !$isAdmin) {
            abort(404, 'Profil penggalang dana tidak ditemukan atau belum disetujui.');
        }

        // Dokumen handling:
        // - Owner atau Admin: dapat melihat seluruh dokumen
        // - Publik: jika organisasi/yayasan/komunitas, hanya dokumen publik. Jika individu, KOSONG (0 dokumen di-load).
        if ($isOwner || $isAdmin) {
            $penggalang->load('penggalangDanaDokumen');
        } elseif ($penggalang->isEntity()) {
            $penggalang->load(['penggalangDanaDokumen' => function ($q) {
                $q->publicOnly();
            }]);
        } else {
            // Penggalang individu: dokumen identitas (KTP dsb) 100% PRIVATE, jangan pernah di-load untuk publik
            $penggalang->setRelation('penggalangDanaDokumen', collect([]));
        }

        // Jika owner, tandai notifikasi sudah dibaca
        if ($isOwner && !$penggalang->status_read) {
            $penggalang->update(['status_read' => true]);
        }

        $legalitasDokumen = $penggalang->penggalangDanaDokumen;

        return view('pages.profil-penggalang', compact('penggalang', 'isOwner', 'isAdmin', 'legalitasDokumen'));
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
        $penggalang = Penggalang_Dana::with('penggalangDanaDokumen')
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        if ($penggalang->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit profil ini.');
        }

        $categories = Penggalang_Dana::getCategoryLabels();

        if ($penggalang->isEntity()) {
            return view('pages.penggalang_dana.edit_organisasi', compact('penggalang', 'categories'));
        }

        return view('pages.penggalang_dana.edit_individu', compact('penggalang', 'categories'));
    }

    /**
     * Update penggalang dana. Hanya owner yang boleh update.
     */
    public function update(Request $request, $id)
    {
        $penggalang = Penggalang_Dana::where('id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        if ($penggalang->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah profil ini.');
        }

        $validated = $request->validate([
            'jenis_penggalang' => 'nullable|string|in:organisasi,yayasan_lembaga,komunitas,individu',
            'nama_penggalang'  => 'required|string|max:255',
            'email'            => 'required|email',
            'no_telepon'       => 'required',
            'alamat'           => 'required',
            'deskripsi'        => 'required',
            'visi'             => 'required',
            'misi'             => 'required',
            'instagram'        => 'nullable',
            'facebook'         => 'nullable',
            'youtube'          => 'nullable',
            'tiktok'           => 'nullable',
            'foto_profil'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'thumbnail'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'tahun_berdiri'    => 'nullable',
            'nama_dokumen'     => 'nullable|array',
            'dokumen_files.*'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'file_dokumen.*'   => 'nullable|string',
        ]);

        // Cek perubahan dokumen
        $oldDokumen = $penggalang->penggalangDanaDokumen()->get();
        $isDokumenChanged = false;

        if ($request->hasFile('dokumen_files')) {
            $isDokumenChanged = true;
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
            $validated['status']             = 'pending';
            $validated['status_read']        = false;
            $validated['catatan_verifikasi'] = null;
            $validated['verified_by']        = null;
            $validated['verified_at']        = null;
            if (in_array($penggalang->status, ['rejected', 'approved'])) {
                $validated['revision_count'] = $penggalang->revision_count + 1;
            }
        }

        $penggalang->update($validated);

        // Jika ada unggahan dokumen baru
        if ($request->hasFile('dokumen_files') || $request->filled('nama_dokumen')) {
            $namaDokumenArr = $request->input('nama_dokumen', []);
            foreach ($namaDokumenArr as $i => $nama) {
                if (empty($nama)) continue;

                if ($request->hasFile("dokumen_files.{$i}")) {
                    $uploaded = $request->file("dokumen_files.{$i}");
                    $storedPath = $uploaded->store("dokumen_penggalang/{$penggalang->id}", 'private');
                    Penggalang_Dana_Dokumen::create([
                        'penggalang_dana_id' => $penggalang->id,
                        'nama_dokumen'       => $nama,
                        'file_dokumen'       => $storedPath,
                        'is_public'          => false,
                        'storage_disk'       => 'private',
                    ]);
                } elseif (!empty($request->file_dokumen[$i])) {
                    Penggalang_Dana_Dokumen::create([
                        'penggalang_dana_id' => $penggalang->id,
                        'nama_dokumen'       => $nama,
                        'file_dokumen'       => trim($request->file_dokumen[$i]),
                        'is_public'          => false,
                        'storage_disk'       => 'private',
                    ]);
                }
            }
        }

        $message = 'Data berhasil diperbarui.';
        if ($isDokumenChanged) {
            $message .= ' Dokumen baru diunggah, status kini PENDING dan menunggu verifikasi ulang.';
        }

        return redirect()
            ->route('profil.penggalang', $penggalang->slug ?? $penggalang->id)
            ->with('success', $message);
    }

    /**
     * Resubmit pengajuan yang ditolak. Hanya owner yang boleh.
     */
    public function resubmit($id)
    {
        $penggalang = Penggalang_Dana::where('id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

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
