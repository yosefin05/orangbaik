<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Update;
use App\Models\Campaign_Update_Gambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'gambar.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'gambar' => 'nullable|array|max:5',
        ]);

        // Simpan update
        $update = Campaign_Update::create([
            'campaign_id' => $campaign->id,
            'user_id' => Auth::id(),
            'judul_update' => $request->judul_update,
            'isi_update' => $request->isi_update,
        ]);

        // Simpan gambar-gambar
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $gambar) {
                $path = $gambar->store('campaign/updates', 'public');

                Campaign_Update_Gambar::create([
                    'campaign_update_id' => $update->id,
                    'gambar_update' => $path,
                ]);
            }
        }

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
            'gambar.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'gambar' => 'nullable|array|max:5',
            'hapus_gambar' => 'nullable|array',
            'hapus_gambar.*' => 'exists:campaign_update_gambar,id',
        ]);

        // Update data
        $update->update([
            'judul_update' => $request->judul_update,
            'isi_update' => $request->isi_update,
        ]);

        // Hapus gambar yang dipilih
        if ($request->has('hapus_gambar')) {
            foreach ($request->hapus_gambar as $gambarId) {
                $gambar = Campaign_Update_Gambar::where('id', $gambarId)
                    ->where('campaign_update_id', $update->id)
                    ->first();
                if ($gambar) {
                    if (Storage::disk('public')->exists($gambar->gambar_update)) {
                        Storage::disk('public')->delete($gambar->gambar_update);
                    }
                    $gambar->delete();
                }
            }
        }

        // Tambah gambar baru
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $gambar) {
                $path = $gambar->store('campaign/updates', 'public');
                Campaign_Update_Gambar::create([
                    'campaign_update_id' => $update->id,
                    'gambar_update' => $path,
                ]);
            }
        }

        return redirect()
            ->route('campaign.show', $campaign->slug)
            ->with('success', 'Update berhasil diperbarui!');
    }

    public function destroy($slug, $id)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        $update = Campaign_Update::where('campaign_id', $campaign->id)
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Hapus gambar-gambar
        foreach ($update->campaign_update_gambar as $gambar) {
            if (Storage::disk('public')->exists($gambar->gambar_update)) {
                Storage::disk('public')->delete($gambar->gambar_update);
            }
            $gambar->delete();
        }

        $update->delete();

        return back()->with('success', 'Update berhasil dihapus.');
    }
}