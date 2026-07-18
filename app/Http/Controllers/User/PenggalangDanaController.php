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
            'nama_dokumen.1' => 'required',

            'file_dokumen.0' => 'required|url',
            'file_dokumen.1' => 'required|url',
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
                ->route('home')
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
            'deskripsi' => 'required',

            'email' => 'required|email',
            'no_telepon' => 'required',

            'instagram' => 'nullable',
            'facebook' => 'nullable',
            'youtube' => 'nullable',
            'tiktok' => 'nullable',

        ]);

        DB::beginTransaction();

        try {

            $fotoProfil = $request
                ->file('foto_profil')
                ->store(
                    'penggalang_dana/profil',
                    'public'
                );

            $penggalangDana = Penggalang_Dana::create([

                'user_id' => auth()->id(),

                'jenis_penggalang' => 'individu',

                // Individu tidak punya banner
                'thumbnail' => null,

                'foto_profil' => $fotoProfil,

                'nama_penggalang' => $request->nama_penggalang,

                // Individu tidak punya tahun berdiri
                'tahun_berdiri' => null,

                'alamat' => $request->alamat,

                'deskripsi' => $request->deskripsi,

                // Visi & misi opsional
                'visi' => $request->visi,
                'misi' => $request->misi,

                'email' => $request->email,

                'no_telepon' => $request->no_telepon,

                'instagram' => $request->instagram,

                'facebook' => $request->facebook,

                'youtube' => $request->youtube,

                'tiktok' => $request->tiktok,

                'status' => 'pending',

                'nama_dokumen' => 'required|array',
                'nama_dokumen.*' => 'required|string',

                'file_dokumen' => 'required|array',
                'file_dokumen.*' => 'required|url',

            ]);
            if ($request->nama_dokumen && $request->file_dokumen) {

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
            }
            DB::commit();

            return redirect()
                ->route('profile.user')
                ->with(
                    'success',
                    'Pengajuan penggalang dana berhasil dikirim dan sedang menunggu verifikasi.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'error' => $e->getMessage()
                ]);
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

        // Upload foto profil
        if ($request->hasFile('foto_profil')) {

            if (
                $penggalang->foto_profil &&
                Storage::disk('public')->exists($penggalang->foto_profil)
            ) {
                Storage::disk('public')->delete($penggalang->foto_profil);
            }

            $validated['foto_profil'] = $request->file('foto_profil')
                ->store('penggalang_dana/profil', 'public');
        }

        // Upload thumbnail
        if ($request->hasFile('thumbnail')) {

            if (
                $penggalang->thumbnail &&
                Storage::disk('public')->exists($penggalang->thumbnail)
            ) {
                Storage::disk('public')->delete($penggalang->thumbnail);
            }

            $validated['thumbnail'] = $request->file('thumbnail')
                ->store('penggalang_dana/thumbnail', 'public');
        }

        // Jika sebelumnya ditolak, berarti ini revisi
        if ($penggalang->status === 'rejected') {

            $validated['status'] = 'pending';
            $validated['status_read'] = false;
            $validated['catatan_verifikasi'] = null;
            $validated['verified_by'] = null;
            $validated['verified_at'] = null;
            $validated['revision_count'] = $penggalang->revision_count + 1;

        }

        // Update data utama
        $penggalang->update($validated);

        // Update dokumen
        if ($request->filled('nama_dokumen')) {

            $penggalang->penggalangDanaDokumen()->delete();

            foreach ($request->nama_dokumen as $i => $nama) {

                if (
                    empty($nama) ||
                    empty($request->file_dokumen[$i])
                ) {
                    continue;
                }

                Penggalang_Dana_Dokumen::create([
                    'penggalang_dana_id' => $penggalang->id,
                    'nama_dokumen' => $nama,
                    'file_dokumen' => $request->file_dokumen[$i],
                ]);
            }
        }

        return redirect()
            ->route('profil.penggalang')
            ->with('success', 'Data berhasil diperbarui.');
    }


}