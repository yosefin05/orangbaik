<?php

use App\Models\Testimoni;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\PenggalangDanaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $testimoni = Testimoni::inRandomOrder()
        ->take(5)
        ->get();

    return view('pages.home', compact('testimoni'));

})->name('home');

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

Route::get('/berita', function () {
    return view('pages.berita');
})->name('berita');

Route::get('/berita/detail', function () {
    return view('pages.detail-berita');
})->name('berita.detail');

Route::get('/tentang', function () {
    return view('pages.tentang');
})->name('tentang');

Route::get('/profile-user', function () {
    return view('pages.profile-user');
})->middleware('auth')->name('profile.user');

Route::get('/verifikasi-penggalang', function () {
    return view('pages.create_individu');
})->name('verifikasi.penggalang');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware([
            'auth',
            'verified'
        ])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Penggalang Dana
    Route::get(
        '/penggalang_dana_organisasi',
        [PenggalangDanaController::class, 'createOrganisasi']
    )->name('penggalang_dana.organisasi.create');

    Route::post(
        '/penggalang_dana_organisasi',
        [PenggalangDanaController::class, 'storeOrganisasi']
    )->name('penggalang_dana.organisasi.store');

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