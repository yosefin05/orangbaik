<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign_Fundraiser;
use Illuminate\Support\Facades\Auth;

class FundraiserDashboardController extends Controller
{
    public function index()
    {
        $fundraisers = Campaign_Fundraiser::with([
            'campaign',
            'donasis' => fn ($q) => $q->whereHas('pembayaran', fn ($p) => $p->where('transaction_status', 'settlement')),
        ])->where('user_id', Auth::id())->where('status', 'active')->get();

        $donations = $fundraisers->flatMap->donasis;

        return view('pages.fundraiser-dashboard', [
            'fundraisers' => $fundraisers,
            'totalDonatur' => $donations->count(),
            'totalDonasi' => $donations->sum('nominal'),
            'totalCampaign' => $fundraisers->pluck('campaign_id')->unique()->count(),
        ]);
    }
}
