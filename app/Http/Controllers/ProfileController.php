<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'nomor' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'password' => 'nullable|confirmed|min:8',
        ]);

        $emailChanged = $validated['email'] !== $user->email;
        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null])->save();
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nomor' => $validated['nomor'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
        ];

        // Simpan foto baru dan hapus foto lama setelah path baru berhasil dibuat.
        if ($request->hasFile('foto_profil')) {
            $oldPhoto = $user->foto_profil;
            $data['foto_profil'] = $request->file('foto_profil')->store('foto-profil', 'public');

            if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with(
            'success',
            $emailChanged
            ? 'Profil diperbarui. Silakan verifikasi email baru Anda.'
            : 'Profil berhasil diperbarui.'
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
