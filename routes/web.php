<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\PenggalangDanaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/donasi', function () {
    return view('pages.donasi');
})->name('donasi');

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