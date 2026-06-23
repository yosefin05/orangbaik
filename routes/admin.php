<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\FilterController;
use App\Http\Controllers\admin\BeritaController;
use App\Http\Controllers\admin\PenggalangDanaController;
use App\Http\Controllers\admin\CampaignController;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');
        Route::resource('filter', FilterController::class)
            ->except(['show']);

        Route::resource('users', UserController::class)
            ->except(['show']);

        Route::resource('berita', BeritaController::class);
        Route::delete(
            '/berita-gambar/{gambar}',
            [BeritaController::class, 'destroyImage']
        )->name('berita-gambar.destroy');
        Route::get(
            '/penggalang_dana',
            [PenggalangDanaController::class, 'index']
        )->name('penggalang_dana.index');

        Route::get(
            '/penggalang_dana/{penggalangDana}',
            [PenggalangDanaController::class, 'show']
        )->name('penggalang_dana.show');

        Route::patch(
            '/penggalang_dana/{penggalangDana}/approve',
            [PenggalangDanaController::class, 'approve']
        )->name('penggalang_dana.approve');

        Route::patch(
            '/penggalang_dana/{penggalangDana}/reject',
            [PenggalangDanaController::class, 'reject']
        )->name('penggalang_dana.reject');

        Route::get(
            '/campaign',
            [CampaignController::class, 'index']
        )->name('campaign.index');

        Route::get(
            '/campaign/{campaign}',
            [CampaignController::class, 'show']
        )->name('campaign.show');

        Route::patch(
            '/campaign/{campaign}/approve',
            [CampaignController::class, 'approve']
        )->name('campaign.approve');

        Route::patch(
            '/campaign/{campaign}/reject',
            [CampaignController::class, 'reject']
        )->name('campaign.reject');
    });