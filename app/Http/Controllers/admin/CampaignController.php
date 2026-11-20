<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Fundraiser;
use App\Models\Campaign_Fundraiser_Alias;
use App\Models\Campaign_Url_Alias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function customUrls()
    {
        $campaigns = Campaign::query()->latest()->paginate(20);
        return view('admin.campaign.custom-url', compact('campaigns'));
    }

    public function updateCustomUrl(Request $request, Campaign $campaign)
    {
        $request->merge([
            'custom_slug' => $request->filled('custom_slug') ? Str::slug($request->custom_slug) : null,
        ]);
        $validated = $request->validate([
            'custom_slug' => Campaign::customSlugRules($campaign->id),
        ]);
        if ($campaign->custom_slug && $campaign->custom_slug !== ($validated['custom_slug'] ?? null)) {
            Campaign_Url_Alias::firstOrCreate([
                'campaign_id' => $campaign->id,
                'custom_slug' => $campaign->custom_slug,
            ]);
        }
        $campaign->update(['custom_slug' => $validated['custom_slug'] ?? null]);
        return back()->with('success', 'Custom URL campaign berhasil diperbarui.');
    }

    public function destroyCustomUrl(Campaign $campaign)
    {
        if ($campaign->custom_slug) {
            Campaign_Url_Alias::firstOrCreate([
                'campaign_id' => $campaign->id,
                'custom_slug' => $campaign->custom_slug,
            ]);
        }
        $campaign->update(['custom_slug' => null]);
        return back()->with('success', 'Custom URL dihapus. Campaign kembali menggunakan URL otomatis.');
    }

    public function updateFundraiserAlias(Request $request, Campaign_Fundraiser $fundraiser)
    {
        $validated = $request->validate(['alias' => Campaign_Fundraiser::aliasRules($fundraiser->id)]);
        $alias = Str::slug($validated['alias']);

        DB::transaction(function () use ($fundraiser, $alias): void {
            if ($fundraiser->alias !== $alias) {
                Campaign_Fundraiser_Alias::firstOrCreate([
                    'campaign_fundraiser_id' => $fundraiser->id,
                    'alias' => $fundraiser->alias,
                ]);
                $fundraiser->update(['alias' => $alias]);
            }
        });
        return back()->with('success', 'Alias fundraiser berhasil diperbarui.');
    }
    public function index()
    {
        $campaign = Campaign::with([
            'kategori',
            'penggalangDana',
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
            'campaignFilter.filter',
            'campaignUpdates.user',
            'campaignFundraisers.user',
            'campaignFundraisers.donasis.pembayaran',
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

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return back()->with('success', 'Campaign berhasil dihapus.');
    }
}
