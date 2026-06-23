<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;

class CampaignController extends Controller
{
    public function index()
    {
        $campaign = Campaign::with([
            'kategori',
            'penggalangDana',
            'campaignGambar',
            'campaignFilter',
            'campaignUpdates'
        ])
            ->latest()
            ->paginate(10);

        return view(
            'admin.campaign.index',
            compact('campaign')
        );
    }

    public function show(Campaign $campaign)
    {
        $campaign->load([
            'kategori',
            'penggalangDana',
            'campaignGambar',
            'campaignFilter.filter',
            'campaignUpdates.user',
            'campaignFundraisers.user',
            'verifier'
        ]);

        return view(
            'admin.campaign.show',
            compact('campaign')
        );
    }

    public function approve(
        Campaign $campaign
    ) {
        $campaign->update([
            'status' => 'approved',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with(
            'success',
            'Campaign berhasil disetujui.'
        );
    }

    public function reject(
        Campaign $campaign
    ) {
        $campaign->update([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with(
            'success',
            'Campaign berhasil ditolak.'
        );
    }
}