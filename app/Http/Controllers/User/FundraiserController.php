<?php
declare(strict_types=1);

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Fundraiser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class FundraiserController extends Controller
{
    public function store($slug)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        if ($campaign->isOwner(Auth::id())) {
            return back()->with('error', 'Anda adalah pemilik campaign, tidak bisa jadi fundraiser.');
        }

        if ($campaign->isFundraiser(Auth::id())) {
            return back()->with('error', 'Anda sudah menjadi fundraiser campaign ini.');
        }

        $fundraiser = Campaign_Fundraiser::create([
            'campaign_id' => $campaign->id,
            'user_id'     => Auth::id(),
            'status'      => 'active',
        ]);

        $this->generateQr($fundraiser);

        return back()->with('success', 'Selamat! Anda sekarang menjadi fundraiser. Link referral Anda: ' . $fundraiser->referral_url);
    }

    public function destroy($slug)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();

        $fundraiser = Campaign_Fundraiser::where('campaign_id', $campaign->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($fundraiser->qr_path) {
            Storage::disk('public')->delete($fundraiser->qr_path);
        }

        $fundraiser->delete();

        return back()->with('success', 'Anda telah berhenti menjadi fundraiser.');
    }

    private function generateQr(Campaign_Fundraiser $fundraiser): void
    {
        try {
            Storage::disk('public')->makeDirectory('qrcode');

            $fileName = 'qrcode/' . $fundraiser->referral_code . '.svg';

            Storage::disk('public')->put(
                $fileName,
                QrCode::format('svg')->size(300)->margin(1)->generate($fundraiser->referral_url)
            );

            $fundraiser->update(['qr_path' => $fileName]);
        } catch (\Exception $e) {
            Log::error('QR Generation failed: ' . $e->getMessage());
        }
    }
}