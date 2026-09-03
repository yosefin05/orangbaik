<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    public function index()
    {
        $berita = Berita::with([
            'user'
        ])
            ->latest()
            ->paginate(10);

        return view(
            'admin.berita.index',
            compact('berita')
        );
    }

    public function show(Berita $beritum)
    {
        $beritum->load([
            'user'
        ]);

        return view(
            'admin.berita.show',
            [
                'berita' => $beritum
            ]
        );
    }

    public function create()
    {
        return view('admin.berita.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'thumbnail' => 'required|image|max:2048',
                'judul' => 'required|max:255',
                'isi' => 'required',

            ]
        );

        DB::transaction(function () use ($request) {

            $thumbnail = $request
                ->file('thumbnail')
                ->store('berita/thumbnail', 'public');

            $berita = Berita::create([
                'thumbnail' => $thumbnail,
                'judul' => $request->judul,
                'isi' => RichText::clean($request->isi),
                'slug' => Str::slug($request->judul),
                'user_id' => Auth::id(),
            ]);

        });

        return redirect()
            ->route('admin.berita.index')
            ->with(
                'success',
                'Berita berhasil ditambahkan'
            );
    }

    public function edit(Berita $beritum)
    {
        return view(
            'admin.berita.edit',
            [
                'berita' => $beritum
            ]
        );
    }

    public function update(
        Request $request,
        Berita $beritum
    ) {
        $request->validate(
            [
                'thumbnail' => 'nullable|image|max:2048',
                'judul' => 'required|max:255',
                'isi' => 'required',

            ]
        );

        $data = [
            'judul' => $request->judul,
            'isi' => RichText::clean($request->isi),
            'slug' => Str::slug($request->judul),
        ];

        if ($request->hasFile('thumbnail')) {
            if ($beritum->thumbnail) {
                Storage::disk('public')
                    ->delete($beritum->thumbnail);
            }

            $data['thumbnail'] = $request
                ->file('thumbnail')
                ->store(
                    'berita/thumbnail',
                    'public'
                );
        }

        $beritum->update($data);

        return redirect()
            ->route(
                'admin.berita.show',
                $beritum
            )
            ->with(
                'success',
                'Berita berhasil diperbarui'
            );
    }

    public function destroy(Berita $beritum)
    {
        Storage::disk('public')
            ->delete($beritum->thumbnail);

        $beritum->delete();

        return redirect()
            ->route('admin.berita.index')
            ->with(
                'success',
                'Berita berhasil dihapus'
            );
    }
}