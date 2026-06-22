<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Berita_Gambar;
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
            'user',
            'gambar'
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
            'user',
            'gambar'
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

                'gambar' => 'nullable|array|max:3',
                'gambar.*' => 'image|max:2048',
            ],
            [
                'gambar.max' => 'Maksimal hanya 3 gambar galeri.',
                'gambar.*.max' => 'Ukuran setiap gambar maksimal 2 MB.',
            ]
        );

        DB::transaction(function () use ($request) {

            $thumbnail = $request
                ->file('thumbnail')
                ->store('berita/thumbnail', 'public');

            $berita = Berita::create([
                'thumbnail' => $thumbnail,
                'judul' => $request->judul,
                'isi' => $request->isi,
                'slug' => Str::slug($request->judul),
                'user_id' => Auth::id(),
            ]);

            if ($request->hasFile('gambar')) {

                foreach ($request->file('gambar') as $gambar) {

                    $path = $gambar->store(
                        'berita/gallery',
                        'public'
                    );

                    Berita_Gambar::create([
                        'berita_id' => $berita->id,
                        'gambar' => $path,
                    ]);
                }
            }
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

                'gambar' => 'nullable|array|max:3',
                'gambar.*' => 'image|max:2048',
            ],
            [
                'gambar.max' => 'Maksimal hanya 3 gambar galeri.',
                'gambar.*.max' => 'Ukuran setiap gambar maksimal 2 MB.',
            ]
        );
        $data = [
            'judul' => $request->judul,
            'isi' => $request->isi,
            'slug' => Str::slug($request->judul),
        ];

        if ($request->hasFile('thumbnail')) {

            Storage::disk('public')
                ->delete($beritum->thumbnail);

            $data['thumbnail'] =
                $request->file('thumbnail')
                    ->store(
                        'berita/thumbnail',
                        'public'
                    );
        }

        if ($request->hasFile('gambar')) {

            foreach ($request->file('gambar') as $gambar) {

                $path = $gambar->store(
                    'berita/gallery',
                    'public'
                );

                Berita_Gambar::create([
                    'berita_id' => $beritum->id,
                    'gambar' => $path,
                ]);
            }
        }

        return redirect()
            ->route('admin.berita.show', $beritum)
            ->with(
                'success',
                'Berita berhasil diperbarui'
            );
    }

    public function destroyImage(
    Berita_Gambar $gambar
)
{
    Storage::disk('public')
        ->delete($gambar->gambar);

    $gambar->delete();

    return back()->with(
        'success',
        'Gambar berhasil dihapus'
    );
}
}