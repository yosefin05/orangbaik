<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Fundraiser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FundraiserController extends Controller
{
    /**
     * Jadi fundraiser (langsung aktif, tanpa approval)
     */
    public function store($slug)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        // Cek apakah user adalah pemilik campaign
        if ($campaign->isOwner(Auth::id())) {
            return back()->with('error', 'Anda adalah pemilik campaign, tidak bisa jadi fundraiser.');
        }

        // Cek apakah sudah jadi fundraiser
        $existing = Campaign_Fundraiser::where('campaign_id', $campaign->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($existing) {
            return back()->with('error', 'Anda sudah menjadi fundraiser campaign ini.');
        }

        // Langsung simpan tanpa approval
        Campaign_Fundraiser::create([
            'campaign_id' => $campaign->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Selamat! Anda sekarang menjadi fundraiser campaign ini.');
    }

    /**
     * Berhenti jadi fundraiser
     */
    public function destroy($slug)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        $fundraiser = Campaign_Fundraiser::where('campaign_id', $campaign->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $fundraiser->delete();

        return back()->with('success', 'Anda telah berhenti menjadi fundraiser.');
    }
}