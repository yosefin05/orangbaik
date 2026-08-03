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
use Illuminate\Support\Str;

class FundraiserController extends Controller
{
    public function store($slug)
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
        
        // ============================================================
        // BUAT URL REFERRAL - LANGSUNG PAKAI CUSTOM_SLUG TANPA PREFIX
        // ============================================================
        // Ambil slug yang benar (custom_slug atau slug biasa)
        $routeSlug = $campaign->custom_slug ?? $campaign->slug;
        
        // BUAT URL MANUAL - langsung pakai slug tanpa prefix /campaign/
        $referralUrl = url('/' . $routeSlug) . '?ref=' . $referralCode;
        
        // Contoh: http://127.0.0.1:8000/po?ref=REF-XXX

        // Buat fundraiser
        $fundraiser = Campaign_Fundraiser::create([
            'campaign_id' => $campaign->id,
            'user_id' => Auth::id(),
            'penggalang_dana_id' => $penggalang->id,
            'referral_code' => $referralCode,
            'referral_url' => $referralUrl,
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