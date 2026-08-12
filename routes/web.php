<?php

use App\Models\Testimoni;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\PenggalangDanaController;
use App\Http\Controllers\User\BeritaController;
use App\Http\Controllers\User\KomentarController;
use App\Http\Controllers\User\KalkulatorController;
use App\Http\Controllers\User\CampaignController;
use App\Http\Controllers\User\DonasiController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\CampaignUpdateController;
use App\Http\Controllers\User\FundraiserController;
use App\Http\Controllers\User\SearchController;
use App\Http\Controllers\User\RiwayatDonasiController;
use App\Http\Controllers\MidtransController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// ============================================================
// HOMEPAGE
// ============================================================
Route::get('/', [HomeController::class, 'index'])->name('home');

// ============================================================
// LOGIN / REGISTER (disediakan oleh Laravel Breeze)
// ============================================================
require __DIR__ . '/auth.php';

// ============================================================
// SET INTENDED URL
// ============================================================
Route::post('/set-intended-url', function (Request $request) {
    session(['url.intended' => $request->url]);
    return response()->json(['success' => true]);
})->name('set.intended.url');

// search
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::post('/midtrans/notification', [MidtransController::class, 'notification']);

// ============================================================
// BERITA
// ============================================================
Route::get('/berita', [BeritaController::class, 'index'])->name('berita.index');
Route::get('/berita/{slug}', [BeritaController::class, 'show'])->name('berita.show');
Route::middleware('auth')->post('/berita/{berita}/komentar', [KomentarController::class, 'store'])
    ->name('berita.komentar.store');

// ============================================================
// KALKULATOR ZAKAT
// ============================================================
Route::get('/kalkulator', [KalkulatorController::class, 'index']);
Route::post('/kalkulator/hitung', [KalkulatorController::class, 'calculate'])->name('kalkulator.hitung');

// ============================================================
// CAMPAIGN (PUBLIC - list)
// ============================================================
Route::get('/donasi', [CampaignController::class, 'index'])->name('donasi');
Route::get('/campaign/{slug}/donasi', [DonasiController::class, 'create'])->name('donasi.create');
Route::post('/campaign/{slug}/donasi', [DonasiController::class, 'store'])->name('donasi.store');
Route::get('/donasi/status/{status}', [DonasiController::class, 'status'])->name('donasi.status');

// ============================================================
// CAMPAIGN (AUTHENTICATED) - semua route dengan prefix /campaign
// ============================================================
Route::middleware('auth')->group(function () {
    // Create campaign
    Route::get('/campaign/create', [CampaignController::class, 'create'])->name('campaign.create');
    Route::post('/campaign', [CampaignController::class, 'store'])->name('campaign.store');

    // Edit campaign
    Route::get('/campaign/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaign.edit');

    // Update & Delete campaign (hanya PUT/DELETE, tidak ada GET)
    Route::put('/campaign/{campaign}', [CampaignController::class, 'update'])->name('campaign.update');
    Route::delete('/campaign/{campaign}', [CampaignController::class, 'destroy'])->name('campaign.destroy');

    // Campaign Update (kabar terbaru) - semua dengan prefix /campaign
    Route::get('/campaign/{slug}/update/create', [CampaignUpdateController::class, 'create'])->name('campaign.update.create');
    Route::post('/campaign/{slug}/update', [CampaignUpdateController::class, 'store'])->name('campaign.update.store');
    Route::get('/campaign/{slug}/update/{update}/edit', [CampaignUpdateController::class, 'edit'])->name('campaign.update.edit');
    Route::put('/campaign/{slug}/update/{update}', [CampaignUpdateController::class, 'update'])->name('campaign.update.update');
    Route::delete('/campaign/{slug}/update/{update}', [CampaignUpdateController::class, 'destroy'])->name('campaign.update.destroy');
    
    // ============================================================
    // FUNDRAISER - PERBAIKI: pindahkan ke sini dengan prefix campaign
    // ============================================================
    Route::post('/campaign/{slug}/fundraiser', [FundraiserController::class, 'store'])
        ->name('fundraiser.store')
        ->where('slug', '.*');
    
    Route::delete('/campaign/{slug}/fundraiser', [FundraiserController::class, 'destroy'])
        ->name('fundraiser.destroy')
        ->where('slug', '.*');
});

// ============================================================
// DONASI (PAGE STATIS)
// ============================================================
Route::get('/donasi/bayar', function () {
    return view('pages.donasi-bayar');
})->name('donasi.bayar');

Route::get('/donasi/bayar-login', function () {
    return view('pages.donasi-bayar-login');
})->middleware('auth')->name('donasi.bayar.login');

// ============================================================
// PUSAT INFORMASI (STATIS)
// ============================================================
Route::get('/tentang', function () {
    return view('pages.tentang');
})->name('tentang');
Route::get('/syarat-ketentuan', function () {
    return view('pages.syarat-ketentuan');
})->name('syarat.ketentuan');
Route::get('/pusat-bantuan', function () {
    return view('pages.pusat-bantuan');
})->name('pusat.bantuan');

// ============================================================
// PENGGALANG DANA (PUBLIC)
// ============================================================
Route::get('/profil-penggalang/{id}', [PenggalangDanaController::class, 'profile'])->name('profil.penggalang');

// ============================================================
// PENGGALANG DANA (AUTHENTICATED)
// ============================================================
Route::middleware('auth')->group(function () {
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
});

// ============================================================
// PROFIL USER (AUTHENTICATED)
// ============================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile-user', function () {
        $penggalang = auth()->user()->penggalangDana()->first();
        return view('pages.profile-user', compact('penggalang'));
    })->name('profile.user');
    Route::get('/riwayat-donasi', [RiwayatDonasiController::class, 'index'])
        ->name('riwayat.donasi')
        ->middleware('auth');
    Route::get('/riwayat-donasi/{donasi}/kwitansi', [RiwayatDonasiController::class, 'kwitansi'])
    ->middleware('auth')
    ->name('riwayat-donasi.kwitansi');
});

// ============================================================
// PROFIL (EDIT, UPDATE, DELETE) - LARAVEL BREEZE
// ============================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================================
// ADMIN ROUTES
// ============================================================
require __DIR__ . '/admin.php';

// ============================================================
// WILDCARD CAMPAIGN - HARUS DI PALING AKHIR!
// Menangani semua URL satu segmen (misal /bantu-korban, /ppp)
// ============================================================
Route::get('/{slug}', [CampaignController::class, 'show'])
    ->where('slug', '.*')  // UBAH: terima semua karakter
    ->name('campaign.show');