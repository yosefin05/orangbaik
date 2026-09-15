<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Update;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampaignUpdateController extends Controller
{
    public function create($slug)
    {
        $campaign = Campaign::where('slug', $slug)
            ->where('penggalang_dana_id', Auth::user()->penggalangDana->id ?? 0)
            ->firstOrFail();

        return view('pages.campaign.update.create', compact('campaign'));
    }

    public function store(Request $request, $slug)
    {
        $campaign = Campaign::where('slug', $slug)
            ->where('penggalang_dana_id', Auth::user()->penggalangDana->id ?? 0)
            ->firstOrFail();

        $request->validate([
            'judul_update' => 'required|string|max:255',
            'isi_update' => 'required|string',
        ]);

        // Simpan update
        $isiUpdate = $this->processImages($request->isi_update);

        $update = Campaign_Update::create([
            'campaign_id' => $campaign->id,
            'user_id' => Auth::id(),
            'judul_update' => $request->judul_update,
            'isi_update' => RichText::clean($isiUpdate),
        ]);

        return redirect()
            ->route('campaign.show', $campaign->slug)
            ->with('success', 'Update berhasil ditambahkan!');
    }

    /**
     * Show edit form
     */
    public function edit($slug, $id)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        $update = Campaign_Update::where('campaign_id', $campaign->id)
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('pages.campaign.update.edit', compact('campaign', 'update'));
    }

    /**
     * Update the update
     */
    public function update(Request $request, $slug, $id)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        $update = Campaign_Update::where('campaign_id', $campaign->id)
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'judul_update' => 'required|string|max:255',
            'isi_update' => 'required|string',
        ]);

        // Update data
        $isiUpdate = $this->processImages($request->isi_update);

        $update->update([
            'judul_update' => $request->judul_update,
            'isi_update' => RichText::clean($isiUpdate),
        ]);

        return redirect()
            ->route('campaign.show', $campaign->slug)
            ->with('success', 'Update berhasil diperbarui!');
    }


    private function processImages(string $html): string
{
    $pattern = '/<img\b([^>]*?)\bsrc=["\']data:image\/(jpeg|jpg|png|webp);base64,([^"\']+)["\']([^>]*)>/i';

    $result = preg_replace_callback(
        $pattern,
        function ($matches) {
            $attributesBefore = $matches[1];
            $extension = strtolower($matches[2]);
            $base64 = $matches[3];
            $attributesAfter = $matches[4];

            // Normalisasi JPG
            if ($extension === 'jpg') {
                $extension = 'jpeg';
            }

            // Decode Base64
            $imageData = base64_decode($base64, true);

            if ($imageData === false) {
                return $matches[0];
            }

            // Pastikan data benar-benar gambar
            $imageInfo = @getimagesizefromstring($imageData);

            if ($imageInfo === false) {
                return $matches[0];
            }

            // Pastikan MIME sesuai dengan format yang diizinkan
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

            // Nama file unik
            $filename = Str::uuid() . '.' . $extension;

            // Simpan ke storage/app/public/campaign-updates
            $path = 'campaign-updates/' . $filename;

            Storage::disk('public')->put($path, $imageData);

            // URL yang disimpan ke database
            $url = '/storage/' . ltrim($path, '/');

            return '<img'
                . $attributesBefore
                . 'src="' . $url . '"'
                . $attributesAfter
                . '>';
        },
        $html
    );

    // preg_replace_callback() bisa mengembalikan null kalau PCRE gagal.
    // Jangan sampai null keluar dari method yang return type-nya string.
    return $result ?? $html;
}

    public function destroy($slug, $id)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        $update = Campaign_Update::where('campaign_id', $campaign->id)
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $update->delete();

        return back()->with('success', 'Update berhasil dihapus.');
    }
}