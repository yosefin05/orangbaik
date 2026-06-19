<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\UserController;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');

    });

Route::get('/users', [UserController::class, 'index'])
    ->name('users.index');

Route::get('/users/{user}', [UserController::class, 'show'])
    ->name('users.show');

Route::get('/users/{user}/edit', [UserController::class, 'edit'])
    ->name('users.edit');

Route::put('/users/{user}', [UserController::class, 'update'])
    ->name('users.update');

Route::delete('/users/{user}', [UserController::class, 'destroy'])
    ->name('users.destroy');