<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Campaign;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenggalangDanaBeritaController extends Controller
{
    /**
     * Cek apakah user adalah penggalang dana yang sudah diverifikasi.
     */
    private function getPenggalangDana()
    {
        return Auth::user()->penggalangDana;
    }

    /**
     * Ambil daftar campaign milik penggalang dana ini.
     */
    private function getMyCampaigns()
    {
        $penggalang = $this->getPenggalangDana();
        if (!$penggalang) {
            return collect();
        }

        return Campaign::where('penggalang_dana_id', $penggalang->id)
            ->orderBy('judul')
            ->get();
    }

    /**
     * Cek apakah berita ini milik penggalang dana yang sedang login.
     * Berita dianggap milik penggalang dana jika dibuat oleh user ini
     * DAN user ini punya relasi penggalang dana.
     */
    private function authorizeBerita(Berita $berita): void
    {
        if (Auth::user()->isAdmin()) {
            return; // Admin boleh semua
        }

        // Penggalang dana hanya boleh akses berita miliknya sendiri
        if ($berita->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola berita ini.');
        }

        // Pastikan dia adalah penggalang dana
        $penggalang = $this->getPenggalangDana();
        if (!$penggalang) {
            abort(403, 'Anda belum terdaftar sebagai Penggalang Dana.');
        }
    }

    /**
     * Form tambah berita (untuk penggalang dana)
     */
    public function create()
    {
        $penggalang = $this->getPenggalangDana();

        if (!$penggalang) {
            return redirect()->route('verifikasi.penggalang')
                ->with('error', 'Anda harus terdaftar sebagai Penggalang Dana untuk menambah berita.');
        }

        if ($penggalang->status !== 'approved' && $penggalang->status !== 'verified') {
            return redirect()->back()
                ->with('error', 'Akun Penggalang Dana Anda belum diverifikasi.');
        }

        $campaigns = $this->getMyCampaigns();

        return view('pages.berita.create', compact('campaigns'));
    }

    /**
     * Simpan berita baru
     */
    public function store(Request $request)
    {
        $penggalang = $this->getPenggalangDana();

        if (!$penggalang) {
            abort(403, 'Anda harus terdaftar sebagai Penggalang Dana.');
        }

        $request->validate([
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'judul'     => 'required|max:255',
            'isi'       => 'required',
            'custom_slug' => 'nullable|alpha_dash|unique:berita,custom_slug',
        ]);

        $thumbnail = $request->file('thumbnail')->store('berita/thumbnail', 'public');

        $isi = $this->processImages($request->isi);

        Berita::create([
            'thumbnail'   => $thumbnail,
            'judul'       => $request->judul,
            'isi'         => RichText::clean($isi),
            'slug'        => Str::slug($request->judul),
            'custom_slug' => $request->custom_slug ? Str::slug($request->custom_slug) : null,
            'user_id'     => Auth::id(),
        ]);

        return redirect()->route('berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    /**
     * Form edit berita
     */
    public function edit(Berita $beritum)
    {
        $this->authorizeBerita($beritum);

        $campaigns = $this->getMyCampaigns();

        return view('pages.berita.edit', [
            'berita'    => $beritum,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Update berita
     */
    public function update(Request $request, Berita $beritum)
    {
        $this->authorizeBerita($beritum);

        $request->validate([
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'judul'       => 'required|max:255',
            'isi'         => 'required',
            'custom_slug' => 'nullable|alpha_dash|unique:berita,custom_slug,' . $beritum->id,
        ]);

        $isi = $this->processImages($request->isi);

        $data = [
            'judul'       => $request->judul,
            'isi'         => RichText::clean($isi),
            'slug'        => Str::slug($request->judul),
            'custom_slug' => $request->custom_slug ? Str::slug($request->custom_slug) : null,
        ];

        if ($request->hasFile('thumbnail')) {
            if ($beritum->thumbnail) {
                Storage::disk('public')->delete($beritum->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('berita/thumbnail', 'public');
        }

        $beritum->update($data);

        return redirect()->route('berita.show', $beritum->custom_slug ?? $beritum->slug)
            ->with('success', 'Berita berhasil diperbarui.');
    }

    /**
     * Hapus berita
     */
    public function destroy(Berita $beritum)
    {
        $this->authorizeBerita($beritum);

        if ($beritum->thumbnail) {
            Storage::disk('public')->delete($beritum->thumbnail);
        }

        $beritum->delete();

        return redirect()->route('berita.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

    /**
     * Process base64 images in rich text content
     */
    private function processImages(string $html): string
    {
        $result = preg_replace_callback(
            '/<img\b([^>]*?)\bsrc=["\']data:image\/(jpeg|jpg|png|webp);base64,([^"\']+)["\']([^>]*)>/i',
            function ($matches) {
                $attributesBefore = $matches[1];
                $extension = strtolower($matches[2]);
                $base64 = $matches[3];
                $attributesAfter = $matches[4];

                if ($extension === 'jpg') {
                    $extension = 'jpeg';
                }

                $imageData = base64_decode($base64, true);
                if ($imageData === false) {
                    return $matches[0];
                }

                $imageInfo = @getimagesizefromstring($imageData);
                if ($imageInfo === false) {
                    return $matches[0];
                }

                $allowedMimeTypes = [
                    'image/jpeg' => 'jpeg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                ];

                $mimeType = $imageInfo['mime'] ?? null;
                if (!isset($allowedMimeTypes[$mimeType])) {
                    return $matches[0];
                }

                $extension = $allowedMimeTypes[$mimeType];
                $filename = Str::uuid() . '.' . $extension;
                $path = 'berita/' . $filename;

                Storage::disk('public')->put($path, $imageData);

                $url = '/storage/' . ltrim($path, '/');

                return '<img'
                    . $attributesBefore
                    . 'src="' . $url . '"'
                    . $attributesAfter
                    . '>';
            },
            $html
        );

        return $result ?? $html;
    }
}
