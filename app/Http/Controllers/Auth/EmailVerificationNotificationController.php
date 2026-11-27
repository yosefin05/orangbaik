<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::warning('Email verification resend failed.', [
                'user_id' => $request->user()->getAuthIdentifier(),
                'exception_class' => get_class($e),
            ]);

            return back()->with('error', 'Email verifikasi belum berhasil dikirim. Periksa alamat email dan coba lagi beberapa saat lagi.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
