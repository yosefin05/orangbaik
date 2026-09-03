<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentGatewayController extends Controller
{
    /**
     * Tampilkan daftar semua payment gateway beserta status konfigurasinya.
     */
    public function index()
    {
        $gateways = PaymentGateway::withCount('channels')
            ->orderBy('id')
            ->get();

        return view('admin.payment.gateway.index', compact('gateways'));
    }

    /**
     * Tambah payment gateway baru langsung dari Admin UI.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:50|unique:payment_gateways,code',
            'driver'        => 'nullable|string|max:50',
            'description'   => 'nullable|string|max:255',
            'api_key'       => 'nullable|string|max:255',
            'server_key'    => 'nullable|string|max:255',
            'client_key'    => 'nullable|string|max:255',
            'webhook_token' => 'nullable|string|max:255',
            'endpoint_url'  => 'nullable|url|max:255',
            'is_production' => 'nullable|boolean',
            'is_active'     => 'nullable|boolean',
        ]);

        $code = Str::slug($request->code);

        $config = [
            'api_key'       => $request->api_key,
            'server_key'    => $request->server_key,
            'client_key'    => $request->client_key,
            'webhook_token' => $request->webhook_token,
            'endpoint_url'  => $request->endpoint_url,
            'is_production' => $request->boolean('is_production', false),
        ];

        PaymentGateway::create([
            'name'        => $request->name,
            'code'        => $code,
            'driver'      => $request->driver ?? ($code === 'midtrans' || $code === 'flip' || $code === 'manual' ? $code : 'generic'),
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'config'      => $config,
        ]);

        return redirect()
            ->route('admin.payment.gateway.index')
            ->with('success', "Payment Gateway \"{$request->name}\" berhasil ditambahkan.");
    }

    /**
     * Update konfigurasi & API Key gateway langsung dari Admin UI.
     */
    public function update(Request $request, PaymentGateway $gateway)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string|max:255',
            'api_key'       => 'nullable|string|max:255',
            'server_key'    => 'nullable|string|max:255',
            'client_key'    => 'nullable|string|max:255',
            'webhook_token' => 'nullable|string|max:255',
            'endpoint_url'  => 'nullable|url|max:255',
            'is_production' => 'nullable|boolean',
            'is_active'     => 'nullable|boolean',
        ]);

        $existingConfig = $gateway->config ?? [];

        $newConfig = array_merge($existingConfig, [
            'api_key'       => $request->filled('api_key') ? $request->api_key : ($existingConfig['api_key'] ?? null),
            'server_key'    => $request->filled('server_key') ? $request->server_key : ($existingConfig['server_key'] ?? null),
            'client_key'    => $request->filled('client_key') ? $request->client_key : ($existingConfig['client_key'] ?? null),
            'webhook_token' => $request->filled('webhook_token') ? $request->webhook_token : ($existingConfig['webhook_token'] ?? null),
            'endpoint_url'  => $request->filled('endpoint_url') ? $request->endpoint_url : ($existingConfig['endpoint_url'] ?? null),
            'is_production' => $request->boolean('is_production', false),
        ]);

        $gateway->update([
            'name'        => $request->name,
            'description' => $request->description ?? $gateway->description,
            'is_active'   => $request->boolean('is_active', $gateway->is_active),
            'config'      => $newConfig,
        ]);

        return redirect()
            ->route('admin.payment.gateway.index')
            ->with('success', "Konfigurasi Gateway \"{$gateway->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus gateway kustom jika belum memiliki payment channel.
     */
    public function destroy(PaymentGateway $gateway)
    {
        if (in_array($gateway->code, ['midtrans', 'flip', 'manual'])) {
            return back()->with('error', "Gateway sistem bawaan \"{$gateway->name}\" tidak dapat dihapus.");
        }

        if ($gateway->channels()->count() > 0) {
            return back()->with('warning', "Gateway \"{$gateway->name}\" masih memiliki channel aktif. Hapus atau pindahkan channel terlebih dahulu.");
        }

        $name = $gateway->name;
        $gateway->delete();

        return redirect()
            ->route('admin.payment.gateway.index')
            ->with('success', "Payment Gateway \"{$name}\" berhasil dihapus.");
    }

    /**
     * Toggle status aktif/nonaktif gateway.
     */
    public function toggleActive(PaymentGateway $gateway)
    {
        $gateway->update(['is_active' => !$gateway->is_active]);

        $status = $gateway->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Gateway {$gateway->name} berhasil {$status}.");
    }
}
