<?php

use App\Models\Testimoni;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\User\{
    PenggalangDanaController,
    BeritaController,
    KomentarController,
    KalkulatorController,
    CampaignController,
    DonasiController,
    HomeController,
    CampaignUpdateController,
    FundraiserController,
    SearchController,
    RiwayatDonasiController,
    PageController,
    PenggalangDashboardController,
    FundraiserDashboardController
};
use App\Http\Controllers\{
    MidtransController,
    FlipController,
    DokumenPenggalangController
};
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Semua route yang butuh verifikasi email ada di group 'verified'
| Route tanpa verifikasi (public) di luar group
*/

// ============================================================
// PUBLIC ROUTES (TIDAK PERLU LOGIN)
// ============================================================

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication (Breeze)
require __DIR__ . '/auth.php';

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');

// ============================================================
// PAYMENT WEBHOOKS (PUBLIC - Dynamic & Modular Gateway Webhooks)
// ============================================================
Route::prefix('payment')->group(function () {
    // Dynamic webhook endpoint: provider code is part of the URL.
    Route::post('/{gateway}/webhook', [PaymentWebhookController::class, 'handle'])
        ->where('gateway', '[a-z0-9-]+')
        ->name('payment.gateway.webhook');

    // Dedicated / legacy aliases
    Route::post('/midtrans/webhook', [MidtransController::class, 'notification'])->name('payment.midtrans.webhook');
    Route::post('/flip/webhook', [FlipController::class, 'notification'])->name('payment.flip.webhook');
});
// Backward compatibility
Route::post('/midtrans/notification', [MidtransController::class, 'notification']);

// ============================================================
// BERITA (PUBLIC + KOMENTAR BUTUH LOGIN)
// ============================================================
Route::get('/berita', [BeritaController::class, 'index'])->name('berita.index');
Route::get('/berita/{slug}', [BeritaController::class, 'show'])->name('berita.show');
Route::post('/berita/{berita}/komentar', [KomentarController::class, 'store'])
    ->middleware('auth')
    ->name('berita.komentar.store');

// ============================================================
// KALKULATOR ZAKAT
// ============================================================
Route::get('/kalkulator-zakat', [KalkulatorController::class, 'index'])->name('kalkulator.index');
Route::get('/kalkulator', fn () => redirect()->route('kalkulator.index', request()->query()));
Route::post('/kalkulator-zakat/hitung', [KalkulatorController::class, 'calculate'])->name('kalkulator.hitung');
Route::post('/kalkulator/hitung', fn () => redirect()->route('kalkulator.hitung', status: 307));

// ============================================================
// CAMPAIGN & DONASI (PUBLIC)
// ============================================================
Route::get('/donasi', [CampaignController::class, 'index'])->name('donasi');
Route::get('/donasi/bayar-login', function () {
    return auth()->check()
        ? redirect()->route('riwayat.donasi')
        : redirect()->route('home');
})->name('donasi.bayar.login');
Route::get('/donasi/{slug}/bayar', [DonasiController::class, 'create'])->name('donasi.create');
Route::post('/donasi/{slug}/bayar', [DonasiController::class, 'store'])->name('donasi.store');
Route::match(['get', 'post'], '/campaign/{slug}/donasi', function (Request $request, string $slug) {
    return redirect()->route('donasi.create', array_merge(['slug' => $slug], $request->query()), status: $request->isMethod('post') ? 307 : 302);
});
Route::get('/donasi/instruksi/{pembayaran}', [DonasiController::class, 'instruksi'])->name('donasi.bayar.instruksi');
Route::post('/donasi/instruksi/{pembayaran}/upload-bukti', [DonasiController::class, 'uploadBukti'])->name('donasi.bayar.upload_bukti');
Route::get('/donasi/instruksi/{pembayaran}/bukti-transfer', [DonasiController::class, 'viewBuktiTransfer'])->name('donasi.bayar.bukti_transfer');
Route::get('/donasi/status/{status}', [DonasiController::class, 'status'])->name('donasi.status');
Route::get('/kwitansi/{donasi}/akses', [RiwayatDonasiController::class, 'kwitansiSigned'])
    ->middleware('signed')
    ->name('kwitansi.signed');

// Static pages
Route::get('/tentang', [PageController::class, 'tentang'])->name('tentang');
Route::get('/syarat-ketentuan', [PageController::class, 'syaratKetentuan'])->name('syarat.ketentuan');
Route::get('/pusat-bantuan', [PageController::class, 'pusatBantuan'])->name('pusat.bantuan');

// Penggalang Dana (Public)
Route::get('/penggalang/{id}', [PenggalangDanaController::class, 'profile'])
    ->where('id', '^(?!dashboard$|rejected$)[A-Za-z0-9][A-Za-z0-9-]*$')
    ->name('profil.penggalang');
Route::get('/profil-penggalang/{id}', function (string $id) {
    $penggalang = \App\Models\Penggalang_Dana::where('id', $id)->orWhere('slug', $id)->firstOrFail();
    return redirect()->route('profil.penggalang', $penggalang);
});
Route::get('/dokumen-penggalang/{id}', [DokumenPenggalangController::class, 'show'])
    ->middleware(['auth', 'admin'])
    ->name('dokumen.show');

// ============================================================
// AUTHENTICATED ROUTES (BUTUH LOGIN, TAPI BELUM TENTU VERIFIED)
// ============================================================
Route::middleware('auth')->group(function () {

    // Dashboard Penggalang Dana
    Route::get('/penggalang/dashboard', [PenggalangDashboardController::class, 'index'])->middleware('verified')->name('penggalang.dashboard');
    Route::get('/fundraiser/dashboard', [FundraiserDashboardController::class, 'index'])->middleware('verified')->name('fundraiser.dashboard');

    // Set intended URL
    Route::post('/set-intended-url', function (Request $request) {
        session(['url.intended' => $request->url]);
        return response()->json(['success' => true]);
    })->name('set.intended.url');

    // Penggalang Dana (CRUD)
    Route::get('/penggalang_dana_organisasi', [PenggalangDanaController::class, 'createOrganisasi'])
        ->middleware('verified')
        ->name('penggalang_dana.organisasi.create');
    Route::post('/penggalang_dana_organisasi', [PenggalangDanaController::class, 'storeOrganisasi'])
        ->middleware('verified')
        ->name('penggalang_dana.organisasi.store');
    Route::get('/verifikasi-penggalang', function () {
        return view('pages.penggalang_dana.create_individu');
    })->middleware('verified')->name('verifikasi.penggalang');
    Route::post('/verifikasi-penggalang', [PenggalangDanaController::class, 'storeIndividu'])
        ->middleware('verified')
        ->name('penggalang_dana.individu.store');
    Route::get('/penggalang/{id}/edit', [PenggalangDanaController::class, 'edit'])
        ->middleware('verified')
        ->name('penggalang_dana.edit');
    Route::patch('/penggalang/{id}', [PenggalangDanaController::class, 'update'])
        ->middleware('verified')
        ->name('penggalang_dana.update');
    Route::delete('/penggalang/{id}/dokumen', [PenggalangDanaController::class, 'destroyDocuments'])
        ->middleware('verified')
        ->name('penggalang_dana.dokumen.destroy');
    Route::get('/penggalang-dana/{id}/edit', fn (string $id) => redirect()->route('penggalang_dana.edit', $id));
    Route::patch('/penggalang-dana/{id}', fn (Request $request, string $id) => redirect()->route('penggalang_dana.update', $id, 307));
    Route::get('/penggalang/rejected', [PenggalangDanaController::class, 'rejected'])
        ->middleware('verified')
        ->name('penggalang_dana.rejected');
    Route::patch('/penggalang-dana/{id}/resubmit', [PenggalangDanaController::class, 'resubmit'])
        ->middleware('verified')
        ->name('penggalang_dana.resubmit');

    // Profile User
    Route::get('/profil', function () {
        $penggalang = auth()->user()->penggalangDana()->first();
        return view('pages.profile-user', compact('penggalang'));
    })->name('profile.user');
    Route::get('/profile-user', fn () => redirect()->route('profile.user'));

    // Riwayat Donasi & Resume Pembayaran
    Route::get('/riwayat-donasi', [RiwayatDonasiController::class, 'index'])->name('riwayat.donasi');
    Route::get('/riwayat-donasi/{donasi}/kwitansi', [RiwayatDonasiController::class, 'kwitansi'])
        ->name('riwayat-donasi.kwitansi');
    Route::get('/donasi/pembayaran/{pembayaran}/resume', [DonasiController::class, 'resume'])
        ->name('donasi.resume');

    // Profile (Breeze)
    Route::get('/profil/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Legacy Breeze URLs tetap aktif agar form/bookmark lama tidak rusak.
    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});

// ============================================================
// AUTHENTICATED + VERIFIED EMAIL ROUTES (WAJIB VERIFIKASI)
// ============================================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Campaign Management
    Route::get('/campaign/create', [CampaignController::class, 'create'])->name('campaign.create');
    Route::post('/campaign', [CampaignController::class, 'store'])->name('campaign.store');
    Route::get('/campaign/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaign.edit');
    Route::put('/campaign/{campaign}', [CampaignController::class, 'update'])->name('campaign.update');
    Route::delete('/campaign/{campaign}', [CampaignController::class, 'destroy'])->name('campaign.destroy');

    // Campaign Updates (Kabar Terbaru)
    Route::prefix('campaign/{slug}/update')->group(function () {
        Route::get('/create', [CampaignUpdateController::class, 'create'])->name('campaign.update.create');
        Route::post('/', [CampaignUpdateController::class, 'store'])->name('campaign.update.store');
        Route::get('/{update}/edit', [CampaignUpdateController::class, 'edit'])->name('campaign.update.edit');
        Route::put('/{update}', [CampaignUpdateController::class, 'update'])->name('campaign.update.update');
        Route::delete('/{update}', [CampaignUpdateController::class, 'destroy'])->name('campaign.update.destroy');
    });

    // Fundraiser
    Route::post('/campaign/{slug}/fundraiser', [FundraiserController::class, 'store'])
        ->name('fundraiser.store')
        ->where('slug', '.*');
    Route::delete('/campaign/{slug}/fundraiser', [FundraiserController::class, 'destroy'])
        ->name('fundraiser.destroy')
        ->where('slug', '.*');
    Route::patch('/fundraiser/{fundraiser}/alias', [FundraiserController::class, 'updateShortAlias'])->name('fundraiser.alias.update');

});

// ============================================================
// ADMIN ROUTES
// ============================================================
require __DIR__ . '/admin.php';

// ============================================================
// URL campaign canonical: slug otomatis berada di /campaign, custom URL di root.
Route::get('/campaign/{slug}', [CampaignController::class, 'show'])->name('campaign.show');
Route::get('/donasi/{slug}', function (string $slug) {
    $campaign = \App\Models\Campaign::where('slug', $slug)->orWhere('custom_slug', $slug)->firstOrFail();
    return redirect($campaign->public_url, 301);
})->name('campaign.legacy.show');
Route::get('/ajak/{alias}', [FundraiserController::class, 'track'])->name('referral.track');
Route::get('/campaign/{campaignSlug}/{fundraiserAlias}', [FundraiserController::class, 'showReferral'])
    ->name('fundraiser.referral.canonical')
    ->where(['campaignSlug' => '[A-Za-z0-9][A-Za-z0-9-]*', 'fundraiserAlias' => '[A-Za-z0-9][A-Za-z0-9_-]*']);
Route::get('/{campaignSlug}/{fundraiserAlias}', [FundraiserController::class, 'showReferral'])
    ->name('fundraiser.referral')
    ->where(['campaignSlug' => '[A-Za-z0-9][A-Za-z0-9-]*', 'fundraiserAlias' => '[A-Za-z0-9][A-Za-z0-9_-]*']);
Route::get('/{slug}', function (string $slug) {
    $campaign = \App\Models\Campaign::where('custom_slug', $slug)->first();
    if ($campaign) {
        return app(CampaignController::class)->show($slug);
    }

    $oldUrl = \App\Models\Campaign_Url_Alias::with('campaign')->where('custom_slug', $slug)->first();
    if ($oldUrl?->campaign) {
        return redirect($oldUrl->campaign->public_url, 301);
    }

    $campaign = \App\Models\Campaign::where('slug', $slug)->first();
    if ($campaign) {
        return redirect()->route('campaign.show', $campaign->slug, 301);
    }

    return app(FundraiserController::class)->trackShort($slug);
})->where('slug', '[A-Za-z0-9][A-Za-z0-9_-]*')->name('campaign.legacy');
