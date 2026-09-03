<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Update;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $update = Campaign_Update::create([
            'campaign_id' => $campaign->id,
            'user_id' => Auth::id(),
            'judul_update' => $request->judul_update,
            'isi_update' => RichText::clean($request->isi_update),
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
        $update->update([
            'judul_update' => $request->judul_update,
            'isi_update' => RichText::clean($request->isi_update),
        ]);

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

        $update->delete();

        return back()->with('success', 'Update berhasil dihapus.');
    }
}