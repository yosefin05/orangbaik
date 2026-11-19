<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\Testimoni;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $testimoni = Testimoni::inRandomOrder()->first();

        return view('auth.register', [
            'testimoni' => $testimoni,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk menggunakan akun Anda.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = DB::transaction(function () use ($request) {
            return User::create([
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'role' => 'user',
            ]);
        });

        // Event verifikasi email dijalankan dengan proteksi try-catch
        // agar kegagalan/timeout SMTP tidak menggagalkan proses registrasi yang sudah dibuat.
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::warning('Email verification event skipped or failed: ' . $e->getMessage());
        }

        // Langsung autentikasi pengguna
        Auth::login($user);

        // Regenerasi session untuk keamanan dan pencegahan session fixation
        $request->session()->regenerate();

        // Redirect langsung ke beranda tanpa harus login ulang
        return redirect()->route('home')->with('success', 'Registrasi berhasil! Selamat datang di OrangBaik.id.');
    }
}
