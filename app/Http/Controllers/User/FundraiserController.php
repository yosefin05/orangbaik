<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Campaign_Fundraiser;
use App\Models\Campaign_Fundraiser_Alias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FundraiserController extends Controller
{
    public function store($slug, Request $request)
    {
        // Cari campaign berdasarkan slug atau custom_slug
        $campaign = Campaign::where('slug', $slug)
            ->orWhere('custom_slug', $slug)
            ->firstOrFail();

        // Cek apakah user sudah menjadi fundraiser
        if ($campaign->isFundraiser(Auth::id())) {
            return back()->with('error', 'Anda sudah menjadi fundraiser untuk campaign ini.');
        }

        // Cek apakah user punya penggalang_dana
        $penggalang = Auth::user()->penggalangDana;
        if (!$penggalang) {
            return back()->with('error', 'Anda harus memiliki profil penggalang dana terlebih dahulu.');
        }

        // Generate referral code yang unik
        $referralCode = 'REF-' . strtoupper(Str::random(8));
        $request->merge([
            'alias' => $request->filled('alias') ? Str::slug($request->alias) : null,
        ]);
        $validated = $request->validate(['alias' => array_merge(['nullable'], array_slice(Campaign_Fundraiser::aliasRules(), 1))]);

        // Buat fundraiser
        $fundraiser = Campaign_Fundraiser::create([
            'campaign_id' => $campaign->id,
            'user_id' => Auth::id(),
            'referral_code' => $referralCode,
            'alias' => $validated['alias'] ?? null,
            'status' => 'active',
        ]);

        // Generate QR Code
        $this->generateQr($fundraiser);

        return back()->with([
            'success' => 'Selamat! Anda sekarang menjadi fundraiser untuk campaign ini.',
            'show_fundraiser_modal' => true,
            'referral_code' => $fundraiser->referral_code,
            'referral_url' => $fundraiser->referral_url,
            'fundraiser_name' => Auth::user()->name,
            'fundraiser_avatar' => Auth::user()->foto_profil ?? 'assets/logo-icon.png',
        ]);
    }

    public function track(string $alias)
    {
        $fundraiser = Campaign_Fundraiser::with('campaign')
            ->where('alias', $alias)->where('status', 'active')->first();
        if (!$fundraiser) {
            $fundraiser = Campaign_Fundraiser_Alias::with('fundraiser.campaign')
                ->where('alias', $alias)->firstOrFail()->fundraiser;
        }
        abort_unless($fundraiser->campaign?->is_active, 404);
        session()->put('campaign_referral.' . $fundraiser->campaign_id, $fundraiser->referral_code);
        return redirect($fundraiser->campaign->public_url);
    }

    public function updateAlias(Request $request, Campaign_Fundraiser $fundraiser)
    {
        abort_unless((int) $fundraiser->user_id === (int) Auth::id(), 403);
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
        return back()->with('success', 'Alias referral berhasil diperbarui.');
    }

    public function destroy($slug)
    {
        $campaign = Campaign::where('slug', $slug)
            ->orWhere('custom_slug', $slug)
            ->firstOrFail();

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
