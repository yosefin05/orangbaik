<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Ubah password user yang sedang login.
 *
 * - Wajib mengisi password saat ini (mencegah pengambilalihan akun
 *   dari sesi yang kebetulan terbuka).
 * - Password baru: min. 8 karakter, huruf + angka, harus berbeda dari
 *   password lama, dan harus dikonfirmasi.
 * - Error masuk ke error bag "updatePassword" supaya tidak bentrok
 *   dengan error form profil di halaman yang sama.
 *
 * Catatan: kalau project (Laravel Breeze) sudah punya
 * App\Http\Controllers\Auth\PasswordController, cukup salin isi method
 * update() ini ke sana dan pakai route "password.update" bawaan Breeze.
 */
class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)->letters()->numbers(),
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
            'password.min' => 'Password baru minimal :min karakter.',
            'password.letters' => 'Password baru harus mengandung huruf.',
            'password.numbers' => 'Password baru harus mengandung angka.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('password_status', 'password-updated');
    }
}