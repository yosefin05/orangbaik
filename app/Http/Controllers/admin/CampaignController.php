<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

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
            'packages', 
            'verifier',
            'approvedBy' 
        ]);

        $campaign->updateActiveStatus();

        return view(
            'admin.campaign.show',
            compact('campaign')
        );
    }

    public function emergencyApprovals()
    {
        $pendingCampaigns = Campaign::whereIn('campaign_type', ['emergency', 'sustainable'])
            ->where('approval_status', 'pending')
            ->where('is_active', true)
            ->with(['penggalangDana', 'kategori'])
            ->latest()
            ->get();

        return view('admin.campaign.emergency-approval', compact('pendingCampaigns'));
    }

    // untuk approve campaign emergency/sustainable
    public function approve(Campaign $campaign)
    {
        // Check if campaign needs approval
        if (!in_array($campaign->campaign_type, ['emergency', 'sustainable'])) {
            return back()->with('error', 'Campaign ini tidak membutuhkan approval.');
        }

        if ($campaign->approval_status === 'approved') {
            return back()->with('error', 'Campaign ini sudah disetujui.');
        }

        $campaign->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Campaign berhasil disetujui.');
    }

    // reject campaign emergency/sustainable
    public function reject(Request $request, Campaign $campaign)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if (!in_array($campaign->campaign_type, ['emergency', 'sustainable'])) {
            return back()->with('error', 'Campaign ini tidak membutuhkan approval.');
        }

        $campaign->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Campaign berhasil ditolak.');
    }

    // unapprove campaign emergency/sustainable
    public function unapprove(Campaign $campaign)
    {
        if (!in_array($campaign->campaign_type, ['emergency', 'sustainable'])) {
            return back()->with('error', 'Campaign ini tidak membutuhkan approval.');
        }

        $campaign->update([
            'approval_status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Approval campaign berhasil dibatalkan.');
    }
}