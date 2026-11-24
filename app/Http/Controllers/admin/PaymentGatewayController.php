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

    /**
     * Dedicated configuration workspace for each built-in payment provider.
     * Secrets are never passed to the view; only presence flags are exposed.
     */
    public function show(PaymentGateway $gateway)
    {
        abort_unless(in_array($gateway->code, ['midtrans', 'flip', 'manual'], true), 404);

        $gateway->loadCount('channels');
        $channels = $gateway->channels()->orderBy('sort_order')->get();
        $routingOptions = PaymentGateway::whereIn('code', ['midtrans', 'flip', 'manual'])->orderBy('id')->get();

        $credentialFlags = [];
        foreach ([
            'sandbox_server_key', 'sandbox_client_key', 'sandbox_signature_secret_key',
            'production_server_key', 'production_client_key', 'production_signature_secret_key',
            'sandbox_api_key', 'production_api_key',
            'sandbox_webhook_token', 'production_webhook_token',
        ] as $key) {
            $credentialFlags[$key] = $gateway->hasConfigValue($key);
        }

        return view('admin.payment.gateway.show', compact('gateway', 'channels', 'credentialFlags', 'routingOptions'));
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
            'sandbox_server_key' => 'nullable|string|max:500',
            'sandbox_client_key' => 'nullable|string|max:500',
            'sandbox_signature_secret_key' => 'nullable|string|max:500',
            'production_server_key' => 'nullable|string|max:500',
            'production_client_key' => 'nullable|string|max:500',
            'production_signature_secret_key' => 'nullable|string|max:500',
            'sandbox_api_key' => 'nullable|string|max:500',
            'production_api_key' => 'nullable|string|max:500',
            'sandbox_webhook_token' => 'nullable|string|max:500',
            'production_webhook_token' => 'nullable|string|max:500',
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
            'sandbox_server_key' => $request->input('sandbox_server_key'),
            'sandbox_client_key' => $request->input('sandbox_client_key'),
            'sandbox_signature_secret_key' => $request->input('sandbox_signature_secret_key'),
            'production_server_key' => $request->input('production_server_key'),
            'production_client_key' => $request->input('production_client_key'),
            'production_signature_secret_key' => $request->input('production_signature_secret_key'),
            'sandbox_api_key' => $request->input('sandbox_api_key'),
            'production_api_key' => $request->input('production_api_key'),
            'sandbox_webhook_token' => $request->input('sandbox_webhook_token'),
            'production_webhook_token' => $request->input('production_webhook_token'),
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
            'signature_secret_key' => 'nullable|string|max:500',
            'webhook_token' => 'nullable|string|max:500',
            'sandbox_server_key' => 'nullable|string|max:500',
            'sandbox_client_key' => 'nullable|string|max:500',
            'sandbox_signature_secret_key' => 'nullable|string|max:500',
            'production_server_key' => 'nullable|string|max:500',
            'production_client_key' => 'nullable|string|max:500',
            'production_signature_secret_key' => 'nullable|string|max:500',
            'sandbox_api_key' => 'nullable|string|max:500',
            'production_api_key' => 'nullable|string|max:500',
            'sandbox_webhook_token' => 'nullable|string|max:500',
            'production_webhook_token' => 'nullable|string|max:500',
            'endpoint_url' => 'prohibited',
            'is_production' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $existingConfig = $gateway->config ?? [];
        $newConfig = $existingConfig;

        foreach ([
            'api_key', 'server_key', 'client_key', 'signature_secret_key', 'webhook_token',
            'sandbox_server_key', 'sandbox_client_key', 'sandbox_signature_secret_key',
            'production_server_key', 'production_client_key', 'production_signature_secret_key',
            'sandbox_api_key', 'production_api_key', 'sandbox_webhook_token', 'production_webhook_token',
        ] as $secretKey) {
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

        // Configuration edits must never change activation state.
        // Gateway activation is managed explicitly from the gateway dashboard.
        
        return redirect()
            ->route('admin.payment.gateway.show', $gateway)
            ->with('success', "Konfigurasi {$gateway->name} berhasil disimpan.");
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
