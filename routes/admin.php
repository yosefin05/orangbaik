<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\FilterController;
use App\Http\Controllers\admin\BeritaController;

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

});