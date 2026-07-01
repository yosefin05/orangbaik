<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function profile()
    {
        $penggalang = auth()->user()
            ->penggalangDana()
            ->with([
                'campaign',
                'penggalangDanaDokumen'
            ])
            ->firstOrFail();

        return view(
            'pages.profil-penggalang',
            compact('penggalang')
        );
    }
}