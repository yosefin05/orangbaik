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

    public function emergencyApprovals()
    {
        $pendingCampaigns = Campaign::whereIn('campaign_type', ['emergency', 'sustainable'])
            ->where('emergency_approval', 'pending')
            ->where('is_active', true)
            ->with(['penggalangDana', 'kategori'])
            ->latest()
            ->get();

        return view('admin.campaign.emergency-approval', compact('pendingCampaigns'));
    }

    /**
     * Menyetujui campaign emergency/sustainable
     */
    public function approveEmergency(Campaign $campaign)
    {
        // Check if campaign needs emergency approval
        if (!in_array($campaign->campaign_type, ['emergency', 'sustainable'])) {
            return back()->with('error', 'Campaign ini tidak membutuhkan approval emergency.');
        }

        // Check if already approved
        if ($campaign->emergency_approval === 'approved') {
            return back()->with('error', 'Campaign ini sudah disetujui.');
        }

        $campaign->update([
            'emergency_approval' => 'approved',
            'emergency_approved_at' => now(),
            'emergency_approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Campaign emergency/sustainable berhasil disetujui.');
    }

    /**
     * Menolak campaign emergency/sustainable
     */
    public function rejectEmergency(Request $request, Campaign $campaign)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if (!in_array($campaign->campaign_type, ['emergency', 'sustainable'])) {
            return back()->with('error', 'Campaign ini tidak membutuhkan approval emergency.');
        }

        $campaign->update([
            'emergency_approval' => 'rejected',
            'emergency_rejection_reason' => $request->rejection_reason,
            'emergency_approved_at' => now(),
            'emergency_approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Campaign emergency/sustainable berhasil ditolak.');
    }
}