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

    public function create()
    {
        return view('admin.berita.create');
    }

    public function show(Berita $beritum)
    {
        $beritum->load('user');

        return view('admin.berita.show', [
            'berita' => $beritum,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'thumbnail' => 'required|image|max:2048',
                'judul' => 'required|max:255',
                'isi' => 'required',
                'custom_slug' => 'nullable|alpha_dash|unique:berita,custom_slug',
            ]
        );

        DB::transaction(function () use ($request) {

            $thumbnail = $request
                ->file('thumbnail')
                ->store('berita/thumbnail', 'public');

            $isi = $this->processImages($request->isi);

            $berita = Berita::create([
                'thumbnail' => $thumbnail,
                'judul' => $request->judul,
                'isi' => RichText::clean($isi),
                'slug' => Str::slug($request->judul),
                'custom_slug' => $request->custom_slug ? Str::slug($request->custom_slug) : null,
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
                'custom_slug' => 'nullable|alpha_dash|unique:berita,custom_slug,' . $beritum->id,
            ]
        );

        $isi = $this->processImages($request->isi);

        $data = [
            'judul' => $request->judul,
            'isi' => RichText::clean($isi),
            'slug' => Str::slug($request->judul),
            'custom_slug' => $request->custom_slug ? Str::slug($request->custom_slug) : null,
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
            ->route('admin.berita.show', $beritum)
            ->with(
                'success',
                'Berita berhasil diperbarui'
            );
    }

    private function processImages(string $html): string
    {
        return preg_replace_callback(
            '/<img([^>]+)src=["\']data:image\/(jpeg|jpg|png);base64,([^"\']+)["\']([^>]*)>/i',
            function ($matches) {
                $attributesBefore = $matches[1];
                $extension = strtolower($matches[2]);
                $base64 = $matches[3];
                $attributesAfter = $matches[4];

                // Decode Base64
                $imageData = base64_decode($base64, true);

                // Kalau gagal decode, biarkan gambar seperti semula
                if ($imageData === false) {
                    return $matches[0];
                }

                // Pastikan ekstensi valid
                if ($extension === 'jpg') {
                    $extension = 'jpeg';
                }

                // Buat nama file unik
                $filename = Str::uuid() . '.' . $extension;

                // Simpan ke storage/app/public/berita
                $path = 'berita/' . $filename;

                Storage::disk('public')->put($path, $imageData);

                // URL yang akan disimpan di database
                $url = Storage::disk('public')->url($path);

                return '<img'
                    . $attributesBefore
                    . 'src="' . $url . '"'
                    . $attributesAfter
                    . '>';
            },
            $html
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