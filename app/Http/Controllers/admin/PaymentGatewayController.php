<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = PaymentGateway::withCount('channels')
            ->orderBy('id')
            ->get();

        return view('admin.payment.gateway.index', compact('gateways'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:payment_gateways,code',
            'driver' => 'nullable|in:midtrans,flip,manual',
            'description' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:500',
            'server_key' => 'nullable|string|max:500',
            'client_key' => 'nullable|string|max:500',
            'signature_secret_key' => 'nullable|string|max:500',
            'webhook_token' => 'nullable|string|max:500',
            'endpoint_url' => 'prohibited',
            'is_production' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $code = Str::slug($request->code);
        if (!in_array($code, ['midtrans', 'flip', 'manual'], true)) {
            return back()->withInput()->withErrors([
                'code' => 'Provider baru belum dapat ditambahkan sebelum adapter integrasinya tersedia.',
            ]);
        }

        $config = PaymentGateway::secureConfigForStorage([
            'api_key' => $request->input('api_key'),
            'server_key' => $request->input('server_key'),
            'client_key' => $request->input('client_key'),
            'signature_secret_key' => $request->input('signature_secret_key'),
            'webhook_token' => $request->input('webhook_token'),
            'is_production' => $request->boolean('is_production'),
        ]);

        $gateway = PaymentGateway::create([
            'name' => $request->name,
            'code' => $code,
            'driver' => $request->input('driver', $code),
            'description' => $request->description,
            'is_active' => false,
            'config' => $config,
        ]);

        if ($code === 'manual') {
            $gateway->update(['is_active' => true]);
        }

        return redirect()
            ->route('admin.payment.gateway.index')
            ->with('success', "Gateway {$gateway->name} berhasil disimpan. Periksa konfigurasi sebelum mengaktifkan.");
    }

    public function update(Request $request, PaymentGateway $gateway)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:500',
            'server_key' => 'nullable|string|max:500',
            'client_key' => 'nullable|string|max:500',
            'webhook_token' => 'nullable|string|max:500',
            'endpoint_url' => 'prohibited',
            'is_production' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $existingConfig = $gateway->config ?? [];
        $newConfig = $existingConfig;

        foreach (['api_key', 'server_key', 'client_key', 'signature_secret_key', 'webhook_token'] as $secretKey) {
            if ($request->filled($secretKey)) {
                $newConfig[$secretKey] = $request->input($secretKey);
            }
        }

        $newConfig['is_production'] = $request->boolean('is_production');
        $newConfig = PaymentGateway::secureConfigForStorage($newConfig);

        $gateway->update([
            'name' => $request->name,
            'description' => $request->input('description', $gateway->description),
            'config' => $newConfig,
        ]);

        // Never activate an API gateway solely because a checkbox was submitted.
        if ($request->boolean('is_active') && $gateway->code === 'manual') {
            $gateway->update(['is_active' => true]);
        } elseif (!$request->boolean('is_active')) {
            $gateway->update(['is_active' => false]);
        }

        return redirect()
            ->route('admin.payment.gateway.index')
            ->with('success', "Konfigurasi {$gateway->name} tersimpan. Status aktivasi tidak berubah otomatis.");
    }

    public function destroy(PaymentGateway $gateway)
    {
        if (in_array($gateway->code, ['midtrans', 'flip', 'manual'], true)) {
            return back()->with('error', "Gateway sistem bawaan {$gateway->name} tidak dapat dihapus.");
        }

        if ($gateway->channels()->exists()) {
            return back()->with('warning', "Gateway {$gateway->name} masih memiliki channel. Nonaktifkan atau pindahkan channel terlebih dahulu.");
        }

        $name = $gateway->name;
        $gateway->delete();

        return redirect()
            ->route('admin.payment.gateway.index')
            ->with('success', "Payment Gateway {$name} berhasil dihapus.");
    }

    public function toggleActive(PaymentGateway $gateway)
    {
        if (!$gateway->is_active && $gateway->code !== 'manual' && !$gateway->isConfigured()) {
            return back()->with('error', 'Gateway belum memiliki kredensial wajib. Lengkapi konfigurasi terlebih dahulu.');
        }

        if (!$gateway->is_active && !in_array($gateway->code, ['midtrans', 'flip', 'manual'], true)) {
            return back()->with('error', 'Provider ini belum memiliki adapter pembayaran yang didukung.');
        }

        $gateway->update(['is_active' => !$gateway->is_active]);
        $status = $gateway->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Gateway {$gateway->name} berhasil {$status}.");
    }
}
