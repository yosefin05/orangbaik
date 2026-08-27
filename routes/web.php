<?php

use App\Models\Testimoni;
use App\Http\Controllers\ProfileController;
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
    RiwayatDonasiController
};
use App\Http\Controllers\{
    MidtransController,
    FlipController
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
// PAYMENT WEBHOOKS (PUBLIC - Midtrans & Flip)
// ============================================================
Route::prefix('payment')->group(function () {
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
Route::get('/kalkulator', [KalkulatorController::class, 'index']);
Route::post('/kalkulator/hitung', [KalkulatorController::class, 'calculate'])->name('kalkulator.hitung');

// ============================================================
// CAMPAIGN & DONASI (PUBLIC)
// ============================================================
Route::get('/donasi', [CampaignController::class, 'index'])->name('donasi');
Route::get('/campaign/{slug}/donasi', [DonasiController::class, 'create'])->name('donasi.create');
Route::post('/campaign/{slug}/donasi', [DonasiController::class, 'store'])->name('donasi.store');
Route::get('/donasi/instruksi/{pembayaran}', [DonasiController::class, 'instruksi'])->name('donasi.bayar.instruksi');
Route::post('/donasi/instruksi/{pembayaran}/upload-bukti', [DonasiController::class, 'uploadBukti'])->name('donasi.bayar.upload_bukti');
Route::get('/donasi/status/{status}', [DonasiController::class, 'status'])->name('donasi.status');

// Static pages
Route::view('/donasi/bayar', 'pages.donasi-bayar')->name('donasi.bayar');
Route::view('/tentang', 'pages.tentang')->name('tentang');
Route::view('/syarat-ketentuan', 'pages.syarat-ketentuan')->name('syarat.ketentuan');
Route::view('/pusat-bantuan', 'pages.pusat-bantuan')->name('pusat.bantuan');

// Penggalang Dana (Public)
Route::get('/profil-penggalang/{id}', [PenggalangDanaController::class, 'profile'])->name('profil.penggalang');

// ============================================================
// AUTHENTICATED ROUTES (BUTUH LOGIN, TAPI BELUM TENTU VERIFIED)
// ============================================================
Route::middleware('auth')->group(function () {
    
    // Set intended URL
    Route::post('/set-intended-url', function (Request $request) {
        session(['url.intended' => $request->url]);
        return response()->json(['success' => true]);
    })->name('set.intended.url');

    // Donasi Bayar (butuh login)
    Route::view('/donasi/bayar-login', 'pages.donasi-bayar-login')->name('donasi.bayar.login');

    // Penggalang Dana (CRUD)
    Route::get('/penggalang_dana_organisasi', [PenggalangDanaController::class, 'createOrganisasi'])
        ->name('penggalang_dana.organisasi.create');
    Route::post('/penggalang_dana_organisasi', [PenggalangDanaController::class, 'storeOrganisasi'])
        ->name('penggalang_dana.organisasi.store');
    Route::get('/verifikasi-penggalang', function () {
        return view('pages.penggalang_dana.create_individu');
    })->name('verifikasi.penggalang');
    Route::post('/verifikasi-penggalang', [PenggalangDanaController::class, 'storeIndividu'])
        ->name('penggalang_dana.individu.store');
    Route::get('/penggalang-dana/{id}/edit', [PenggalangDanaController::class, 'edit'])
        ->name('penggalang_dana.edit');
    Route::patch('/penggalang-dana/{id}', [PenggalangDanaController::class, 'update'])
        ->name('penggalang_dana.update');
    Route::get('/penggalang/rejected', [PenggalangDanaController::class, 'rejected'])
        ->name('penggalang_dana.rejected');
    Route::patch('/penggalang-dana/{id}/resubmit', [PenggalangDanaController::class, 'resubmit'])
        ->name('penggalang_dana.resubmit');

    // Profile User
    Route::get('/profile-user', function () {
        $penggalang = auth()->user()->penggalangDana()->first();
        return view('pages.profile-user', compact('penggalang'));
    })->name('profile.user');

    // Riwayat Donasi
    Route::get('/riwayat-donasi', [RiwayatDonasiController::class, 'index'])->name('riwayat.donasi');
    Route::get('/riwayat-donasi/{donasi}/kwitansi', [RiwayatDonasiController::class, 'kwitansi'])
        ->name('riwayat-donasi.kwitansi');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
});

// ============================================================
// ADMIN ROUTES
// ============================================================
require __DIR__ . '/admin.php';

// ============================================================
// WILDCARD CAMPAIGN - HARUS DI PALING AKHIR!
// ============================================================
Route::get('/{slug}', [CampaignController::class, 'show'])
    ->where('slug', '.*')
    ->name('campaign.show');