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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class PenggalangDanaController extends Controller
{
    public function createOrganisasi(Request $request)
    {
        $existing = $request->user()->penggalangDana;
        if ($existing) {
            return redirect()->route('profile.user')->with('warning', 'Anda sudah memiliki pengajuan Penggalang Dana dengan status: ' . strtoupper($existing->status));
        }

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
        if ($request->user()->penggalangDana()->exists()) {
            return redirect()->route('profile.user')->with('warning', 'Pengajuan Penggalang Dana Anda sudah tercatat.');
        }

        $request->merge(self::normalizedContactInput($request));
        $request->validate([
            'jenis_penggalang' => 'nullable|string|in:organisasi,yayasan_lembaga,komunitas',
            'thumbnail'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'foto_profil'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'nama_penggalang'  => 'required|max:255',
            'tahun_berdiri'    => 'required|digits:4',
            'alamat'           => 'required',
            'deskripsi'        => 'required',
            'visi'             => 'required',
            'misi'             => 'required',
            'email'            => 'required|email',
            'no_telepon'       => ['required', 'regex:/^62[0-9]{8,13}$/'],
            'nama_dokumen'     => 'required|array|min:1',
            'nama_dokumen.0'   => 'required|string',
            'dokumen_files.*'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'file_dokumen.*'   => 'nullable|url|max:2048',
            'file_dokumen.0'   => 'required_without:dokumen_files.0|nullable|url|max:2048',
            'instagram'        => 'nullable|string|max:255',
            'facebook'         => 'nullable|string|max:255',
            'youtube'          => 'nullable|string|max:255',
            'tiktok'           => 'nullable|string|max:255',
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
            $thumbnail = $request->hasFile('thumbnail')
                ? $request->file('thumbnail')->store('penggalang_dana/thumbnail', 'public')
                : null;

            $fotoProfil = $request->hasFile('foto_profil')
                ? $request->file('foto_profil')->store('penggalang_dana/profil', 'public')
                : null;

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

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Fundraiser organization submission failed.', [
                'user_id' => $request->user()?->getAuthIdentifier(),
                'exception_class' => get_class($e),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan. Silakan coba lagi atau hubungi admin.');
        }
    }

    public function storeIndividu(Request $request)
    {
        if ($request->user()->penggalangDana()->exists()) {
            return redirect()->route('profile.user')->with('warning', 'Pengajuan Penggalang Dana Anda sudah tercatat.');
        }

        $request->merge(self::normalizedContactInput($request));
        $request->validate([
            'foto_profil'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'nama_penggalang' => 'required|max:255',
            'alamat'          => 'required',
            'deskripsi'       => 'nullable',
            'email'           => 'required|email',
            'no_telepon'      => ['required', 'regex:/^62[0-9]{8,13}$/'],
            'nama_dokumen'    => 'required|array|min:1',
            'nama_dokumen.0'  => 'required|string',
            'dokumen_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'file_dokumen.*'  => 'nullable|url|max:2048',
            'visi'            => 'nullable|string|max:5000',
            'misi'            => 'nullable|string|max:10000',
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
            $fotoProfil = $request->hasFile('foto_profil')
                ? $request->file('foto_profil')->store('penggalang_dana/profil', 'public')
                : null;

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

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Fundraiser individual submission failed.', [
                'user_id' => $request->user()?->getAuthIdentifier(),
                'exception_class' => get_class($e),
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan. Silakan coba lagi atau hubungi admin.');
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
            // Pemilik/admin perlu melihat campaign pending/rejected untuk memantau workflow.
            $penggalang->load('campaign');
        } elseif ($penggalang->isEntity()) {
            $penggalang->setRelation('campaign', $penggalang->campaign
                ->filter(fn ($campaign) => $campaign->is_active && $campaign->isApproved())
                ->values());
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

        Gate::authorize('update', $penggalang);

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

        Gate::authorize('update', $penggalang);

        $request->merge(self::normalizedContactInput($request));
        $validated = $request->validate([
            'jenis_penggalang' => 'nullable|string|in:organisasi,yayasan_lembaga,komunitas,individu',
            'nama_penggalang'  => 'required|string|max:255',
            'email'            => 'required|email',
            'no_telepon'       => ['required', 'regex:/^62[0-9]{8,13}$/'],
            'alamat'           => 'required',
            'deskripsi'        => $penggalang->isIndividu() ? 'nullable|string' : 'required|string',
            'visi'             => $penggalang->isIndividu() ? 'nullable|string|max:5000' : 'required|string|max:5000',
            'misi'             => $penggalang->isIndividu() ? 'nullable|string|max:10000' : 'required|string|max:10000',
            'instagram'        => 'nullable',
            'facebook'         => 'nullable',
            'youtube'          => 'nullable',
            'tiktok'           => 'nullable',
            'foto_profil'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'thumbnail'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_foto_profil' => 'nullable|boolean',
            'remove_thumbnail' => 'nullable|boolean',
            'tahun_berdiri'    => 'nullable',
            'nama_dokumen'     => 'nullable|array',
            'dokumen_files.*'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'file_dokumen.*'   => 'nullable|url|max:2048',
        ]);

        // Cek perubahan dokumen
        $isDokumenChanged = false;
        $documentsToDelete = [];
        $importantFields = ['nama_penggalang', 'jenis_penggalang', 'no_telepon', 'alamat'];
        $requiresProfileApproval = $penggalang->status === 'approved' && $penggalang->verified
            && collect($importantFields)->contains(fn ($field) => array_key_exists($field, $validated)
                && (string) $validated[$field] !== (string) $penggalang->{$field});

        // Upload foto profil
        if ($request->boolean('remove_foto_profil') && !$request->hasFile('foto_profil')) {
            if ($penggalang->foto_profil && Storage::disk('public')->exists($penggalang->foto_profil)) {
                Storage::disk('public')->delete($penggalang->foto_profil);
            }
            $validated['foto_profil'] = null;
        } elseif ($request->hasFile('foto_profil')) {
            $newFotoProfil = $request->file('foto_profil')
                ->store('penggalang_dana/profil', 'public');
            if ($penggalang->foto_profil && Storage::disk('public')->exists($penggalang->foto_profil)) {
                Storage::disk('public')->delete($penggalang->foto_profil);
            }
            $validated['foto_profil'] = $newFotoProfil;
        }

        // Upload thumbnail
        if ($request->boolean('remove_thumbnail') && !$request->hasFile('thumbnail')) {
            if ($penggalang->thumbnail && Storage::disk('public')->exists($penggalang->thumbnail)) {
                Storage::disk('public')->delete($penggalang->thumbnail);
            }
            $validated['thumbnail'] = null;
        } elseif ($request->hasFile('thumbnail')) {
            $newThumbnail = $request->file('thumbnail')
                ->store('penggalang_dana/thumbnail', 'public');
            if ($penggalang->thumbnail && Storage::disk('public')->exists($penggalang->thumbnail)) {
                Storage::disk('public')->delete($penggalang->thumbnail);
            }
            $validated['thumbnail'] = $newThumbnail;
        }

        // Simpan dokumen baru terlebih dahulu. Dokumen lama hanya dihapus
        // setelah record baru berhasil disimpan agar tidak terjadi kehilangan data.
        if ($request->hasFile('dokumen_files') || $request->filled('nama_dokumen')) {
            $namaDokumenArr = $request->input('nama_dokumen', []);
            $existingDokumen = $penggalang->penggalangDanaDokumen()->orderBy('id')->get();

            foreach ($namaDokumenArr as $i => $nama) {
                $nama = trim((string) $nama);
                if ($nama === '') {
                    continue;
                }

                $documentId = $request->input("dokumen_ids.{$i}");
                $document = $documentId
                    ? $existingDokumen->firstWhere('id', (int) $documentId)
                    : $existingDokumen->get($i);
                $oldPath = $document?->file_dokumen;
                $oldDisk = $document?->storage_disk ?? 'private';
                $newPath = null;
                $newDisk = 'private';

                if ($request->hasFile("dokumen_files.{$i}")) {
                    $newPath = $request->file("dokumen_files.{$i}")
                        ->store("dokumen_penggalang/{$penggalang->id}", 'private');
                } else {
                    $candidateUrl = trim((string) $request->input("file_dokumen.{$i}"));
                    if ($candidateUrl !== '') {
                        $newPath = $candidateUrl;
                    }
                }

                if (!$newPath) {
                    if ($document && $document->nama_dokumen !== $nama) {
                        $document->update(['nama_dokumen' => $nama]);
                    }
                    continue;
                }

                $pathChanged = !$document || $oldPath !== $newPath || $oldDisk !== $newDisk;
                $data = [
                    'nama_dokumen' => $nama,
                    'file_dokumen' => $newPath,
                    'is_public' => $document?->is_public ?? false,
                    'storage_disk' => $newDisk,
                ];

                if ($document) {
                    $document->update($data);
                } else {
                    $data['penggalang_dana_id'] = $penggalang->id;
                    $document = Penggalang_Dana_Dokumen::create($data);
                }

                if ($pathChanged) {
                    $isDokumenChanged = true;
                    if ($oldPath && $oldPath !== $newPath && !filter_var($oldPath, FILTER_VALIDATE_URL)) {
                        $documentsToDelete[] = [$oldDisk, $oldPath];
                    }
                }
            }
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

        if ($requiresProfileApproval && Schema::hasColumn('penggalang_dana', 'pending_changes')) {
            $pending = $penggalang->pending_changes ?? [];
            foreach ($importantFields as $field) {
                if (array_key_exists($field, $validated)) {
                    $pending[$field] = $validated[$field];
                    unset($validated[$field]);
                }
            }
            $validated['pending_changes'] = $pending;
        }

        $penggalang->update($validated);

        foreach ($documentsToDelete as [$oldDisk, $oldPath]) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        $message = 'Data berhasil diperbarui.';
        if ($isDokumenChanged) {
            $message .= ' Dokumen baru diunggah, status kini PENDING dan menunggu verifikasi ulang.';
        } elseif ($requiresProfileApproval) {
            $message .= ' Perubahan identitas penting menunggu persetujuan admin; data lama tetap tampil di publik.';
        }

        return redirect()
            ->route('profil.penggalang', $penggalang->slug ?? $penggalang->id)
            ->with('success', $message);
    }

    /**
     * Hapus satu atau beberapa dokumen legalitas milik penggalang.
     * File fisik hanya dihapus jika tidak lagi dipakai record dokumen lain.
     */
    public function destroyDocuments(Request $request, $id)
    {
        $penggalang = Penggalang_Dana::where('id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        // Penghapusan dokumen hanya boleh dilakukan oleh pemilik akun penggalang.
        // Admin bertugas memverifikasi dan tidak boleh menghapus dokumen milik penggalang.
        if ((int) $penggalang->user_id !== (int) auth()->id()) {
            abort(403, 'Hanya pemilik akun penggalang yang dapat menghapus dokumen.');
        }

        if ($penggalang->status === 'pending') {
            return back()->with('error', 'Dokumen tidak dapat dihapus selama proses verifikasi berlangsung.');
        }

        $validated = $request->validate([
            'dokumen_ids' => 'required|array|min:1',
            'dokumen_ids.*' => 'required|integer|distinct',
        ]);

        $documents = $penggalang->penggalangDanaDokumen()
            ->whereIn('id', $validated['dokumen_ids'])
            ->get();

        if ($documents->count() !== count(array_unique($validated['dokumen_ids']))) {
            abort(404, 'Satu atau beberapa dokumen tidak ditemukan pada profil ini.');
        }

        $filesToCheck = $documents
            ->filter(fn ($document) => $document->file_dokumen && !filter_var($document->file_dokumen, FILTER_VALIDATE_URL))
            ->map(fn ($document) => [
                'disk' => $document->storage_disk ?: 'private',
                'path' => $document->file_dokumen,
            ])
            ->unique(fn ($file) => $file['disk'] . '|' . $file['path'])
            ->values()
            ->all();

        DB::transaction(function () use ($documents, $penggalang) {
            foreach ($documents as $document) {
                $document->delete();
            }

            if (in_array($penggalang->status, ['approved', 'rejected'], true)) {
                $penggalang->update([
                    'status' => 'pending',
                    'status_read' => false,
                    'catatan_verifikasi' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                    'revision_count' => (int) $penggalang->revision_count + 1,
                ]);
            }
        });

        foreach ($filesToCheck as $file) {
            $stillUsedQuery = Penggalang_Dana_Dokumen::where('file_dokumen', $file['path']);
            if ($file['disk'] === 'private') {
                $stillUsedQuery->where(function ($query) {
                    $query->where('storage_disk', 'private')->orWhereNull('storage_disk');
                });
            } else {
                $stillUsedQuery->where('storage_disk', $file['disk']);
            }

            if (!$stillUsedQuery->exists()) {
                Storage::disk($file['disk'])->delete($file['path']);
            }
        }

        return redirect()
            ->route('penggalang_dana.edit', $penggalang->slug ?? $penggalang->id)
            ->with('success', $documents->count() . ' dokumen berhasil dihapus.');
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

    private static function normalizedContactInput(Request $request): array
    {
        $data = [];
        if ($request->has('no_telepon')) {
            $digits = preg_replace('/\D+/', '', (string) $request->input('no_telepon'));
            if (str_starts_with($digits, '0062')) {
                $digits = substr($digits, 2);
            }
            $data['no_telepon'] = str_starts_with($digits, '0')
                ? '62' . substr($digits, 1)
                : $digits;
        }

        foreach (['instagram', 'tiktok'] as $field) {
            if ($request->has($field)) {
                $data[$field] = ltrim(trim((string) $request->input($field)), '@');
            }
        }

        return $data;
    }
}
