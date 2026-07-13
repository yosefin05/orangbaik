<?php

use App\Models\Testimoni;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\PenggalangDanaController;
use App\Http\Controllers\User\BeritaController;
use App\Http\Controllers\User\KomentarController;
use App\Http\Controllers\User\KalkulatorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// login register
Route::get('/', function () {

    $testimoni = Testimoni::inRandomOrder()
        ->take(5)
        ->get();

    return view('pages.home', compact('testimoni'));

})->name('home');

Route::post('/set-intended-url', function (Request $request) {
    session(['url.intended' => $request->url]);

    return response()->json([
        'success' => true
    ]);
})->name('set.intended.url');

// berita
Route::get('/berita', [BeritaController::class, 'index'])
    ->name('berita.index');

Route::get('/berita/{slug}', [BeritaController::class, 'show'])
    ->name('berita.show');

Route::middleware('auth')->post(
    '/berita/{berita}/komentar',
    [KomentarController::class, 'store']
)->name('berita.komentar.store');

Route::get('/home', function () {
    return view('home');
})->middleware([
            'auth',
            'verified'
        ])->name('home');

// kalkulator zakat
Route::get('/kalkulator', [KalkulatorController::class, 'index']);
Route::post('/kalkulator/hitung', [KalkulatorController::class, 'calculate'])->name('kalkulator.hitung');

// donasi dan campaign
Route::get('/donasi', function () {
    return view('pages.donasi');
})->name('donasi');

Route::get('/campaign/detail', function () {
    return view('pages.detail-campaign');
})->name('campaign.detail');

Route::get('/donasi/bayar', function () {
    return view('pages.donasi-bayar');
})->name('donasi.bayar');

Route::get('/donasi/bayar-login', function () {
    return view('pages.donasi-bayar-login');
})->middleware('auth')->name('donasi.bayar.login');

// pusat informasi
Route::get('/tentang', function () {
    return view('pages.tentang');
})->name('tentang');

Route::get('/syarat-ketentuan', function () {
    return view('pages.syarat-ketentuan');
})->name('syarat.ketentuan');

Route::get('/pusat-bantuan', function () {
    return view('pages.pusat-bantuan');
})->name('pusat.bantuan');

Route::get('/campaign/create', function () {
    return view('pages.create');
});

// profile pengguna
Route::get('/profile-user', function () {

    $penggalang = auth()->user()
        ->penggalangDana()
        ->first();

    return view('pages.profile-user', compact('penggalang'));

})->middleware('auth')->name('profile.user');

// Halaman Pengajuan Ditolak
Route::get(
    '/penggalang/rejected',
    [PenggalangDanaController::class, 'rejected']
)->middleware('auth')->name('penggalang_dana.rejected');

Route::get('/riwayat-donasi', function () {
    return view('pages.riwayat-donasi');
})->middleware('auth')->name('riwayat.donasi');

Route::get(
    '/profil-penggalang',
    [PenggalangDanaController::class, 'profile']
)->name('profil.penggalang');

// penggalang dana 
Route::middleware('auth')->group(function () {
    Route::get(
        '/penggalang_dana_organisasi',
        [PenggalangDanaController::class, 'createOrganisasi']
    )->name('penggalang_dana.organisasi.create');

    Route::post(
        '/penggalang_dana_organisasi',
        [PenggalangDanaController::class, 'storeOrganisasi']
    )->name('penggalang_dana.organisasi.store');

    Route::get('/verifikasi-penggalang', function () {
        return view('pages.penggalang_dana.create_individu');
    })->name('verifikasi.penggalang');

    Route::post('/verifikasi-penggalang', [PenggalangDanaController::class, 'storeIndividu'])
        ->name('penggalang_dana.individu.store');

    Route::get('/penggalang-dana/{id}/edit', [PenggalangDanaController::class, 'edit'])
        ->name('penggalang_dana.edit');

    Route::patch('/penggalang-dana/{id}', [PenggalangDanaController::class, 'update'])
        ->name('penggalang_dana.update');

    // profil
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';