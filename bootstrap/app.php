<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'midtrans/notification',
            'payment/*/webhook',
            'payment/midtrans/webhook',
            'payment/flip/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman.',
                ], 419);
            }

            $fallbackRoute = $request->is('login') ? route('login') : ($request->is('register') ? route('register') : route('home'));

            return redirect()->back(fallback: $fallbackRoute)
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('error', 'Sesi Anda telah kedaluwarsa atau token keamanan tidak valid. Halaman telah diperbarui, silakan coba kembali.');
        });
    })->create();
