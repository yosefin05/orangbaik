<?php

use App\Models\Testimoni;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\FilterController;
use App\Http\Controllers\admin\BeritaController;
use App\Http\Controllers\admin\PenggalangDanaController;
use App\Http\Controllers\admin\CampaignController;
use App\Http\Controllers\admin\TestimoniController;
use App\Http\Controllers\admin\KomentarController;
use App\Http\Controllers\admin\DonasiController;
use App\Http\Controllers\admin\PaymentGatewayController;
use App\Http\Controllers\admin\PaymentChannelController;

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
        Route::resource('komentar', KomentarController::class)
            ->only(['index', 'show', 'destroy']);

        Route::get(
            '/penggalang_dana',
            [PenggalangDanaController::class, 'index']
        )->name('penggalang_dana.index');
        Route::get(
            '/penggalang_dana/{penggalangDana}',
            [PenggalangDanaController::class, 'show']
        )->name('penggalang_dana.show');
        Route::delete(
            '/penggalang_dana/{penggalangDana}',
            [PenggalangDanaController::class, 'destroy']
        )->name('penggalang_dana.destroy');
        Route::patch(
            '/penggalang_dana/{penggalangDana}/approve',
            [PenggalangDanaController::class, 'approve']
        )->name('penggalang_dana.approve');
        Route::patch(
            '/penggalang_dana/{penggalangDana}/reject',
            [PenggalangDanaController::class, 'reject']
        )->name('penggalang_dana.reject');
        Route::patch('/penggalang_dana/{penggalangDana}/verify', [PenggalangDanaController::class, 'verify'])
            ->name('penggalang_dana.verify');
        Route::patch('/penggalang_dana/{penggalangDana}/unverify', [PenggalangDanaController::class, 'unverify'])
            ->name('penggalang_dana.unverify');

        // ==================== CAMPAIGN ROUTES ===================
        Route::prefix('campaign')->name('campaign.')->group(function () {

            // List & Detail
            Route::get('/', [CampaignController::class, 'index'])->name('index');
            Route::get('/{campaign}', [CampaignController::class, 'show'])->name('show');

            // ============================================
            // APPROVAL ROUTES - TAMBAHKAN INI!
            // ============================================
            Route::post('/{campaign}/approve', [CampaignController::class, 'approve'])->name('approve');
            Route::post('/{campaign}/reject', [CampaignController::class, 'reject'])->name('reject');
            Route::post('/{campaign}/unapprove', [CampaignController::class, 'unapprove'])->name('unapprove');
        });

        Route::resource('testimoni', TestimoniController::class);

        Route::get('donasi/export', [DonasiController::class, 'export'])->name('donasi.export');
        Route::patch('donasi/{donasi}/approve-manual', [DonasiController::class, 'approveManual'])->name('donasi.approve-manual');
        Route::patch('donasi/{donasi}/reject-manual', [DonasiController::class, 'rejectManual'])->name('donasi.reject-manual');
        Route::resource('donasi', DonasiController::class);

        // ==================== PAYMENT MANAGEMENT ====================
        Route::prefix('payment')->name('payment.')->group(function () {

            // Payment Gateway (CRUD + toggle status + config)
            Route::get('/gateway', [PaymentGatewayController::class, 'index'])
                ->name('gateway.index');
            Route::post('/gateway', [PaymentGatewayController::class, 'store'])
                ->name('gateway.store');
            Route::put('/gateway/{gateway}', [PaymentGatewayController::class, 'update'])
                ->name('gateway.update');
            Route::delete('/gateway/{gateway}', [PaymentGatewayController::class, 'destroy'])
                ->name('gateway.destroy');
            Route::patch('/gateway/{gateway}/toggle', [PaymentGatewayController::class, 'toggleActive'])
                ->name('gateway.toggle');

            // Payment Channel (CRUD + sort + toggle + batch)
            Route::post('/channel/batch', [PaymentChannelController::class, 'batchUpdate'])
                ->name('channel.batch');
            Route::resource('channel', PaymentChannelController::class)->except(['show']);
            Route::patch('/channel/sort', [PaymentChannelController::class, 'updateSort'])
                ->name('channel.sort');
            Route::patch('/channel/{channel}/toggle', [PaymentChannelController::class, 'toggleActive'])
                ->name('channel.toggle');
        });
    });