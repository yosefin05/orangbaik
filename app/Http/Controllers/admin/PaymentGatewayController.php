<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\PaymentGateways\PaymentGatewayConnectivityVerifier;
use App\Services\PaymentGateways\PublicGatewayEndpoint;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

        $gatewayAdminData = $gateways->mapWithKeys(function (PaymentGateway $gateway): array {
            return [$gateway->id => [
                'id'          => $gateway->id,
                'name'        => $gateway->name,
                'code'        => $gateway->code,
                'description' => $gateway->description,
                'is_active'   => $gateway->is_active,
                'config'      => [
                    'is_production'  => (bool) $gateway->getConfigValue('is_production', false),
                    'endpoint_url'   => $gateway->getConfigValue('endpoint_url', ''),
                    'verification_url' => $gateway->getConfigValue('verification_url', ''),
                    'has_api_key'    => $gateway->hasConfigValue('api_key'),
                    'has_server_key' => $gateway->hasConfigValue('server_key'),
                    'has_client_key' => $gateway->hasConfigValue('client_key'),
                    'has_webhook'    => $gateway->hasConfigValue('webhook_token'),
                    'is_configured'  => $gateway->isConfigured(),
                    'is_verified'    => $gateway->isOperationallyVerified(),
                    'verification_reason' => $gateway->connectivityVerificationReason(),
                    'verified_at'    => $gateway->config['connectivity_verified_at'] ?? null,
                ],
            ]];
        });

        $appKeyAvailable = PaymentGateway::encryptionKeyAvailable();

        return view('admin.payment.gateway.index', compact('gateways', 'gatewayAdminData', 'appKeyAvailable'));
    }

    /**
     * Tambah payment gateway baru langsung dari Admin UI.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:50',
            'driver'        => 'nullable|in:generic,custom',
            'description'   => 'nullable|string|max:255',
            'api_key'       => 'nullable|string|max:255',
            'server_key'    => 'nullable|string|max:255',
            'client_key'    => 'nullable|string|max:255',
            'webhook_token' => 'nullable|string|max:255',
            'endpoint_url'  => 'nullable|url|max:255',
            'verification_url' => 'nullable|url|max:255',
            'is_production' => 'nullable|boolean',
            'is_active'     => 'nullable|boolean',
        ]);

        $this->validateEndpoint($validated['endpoint_url'] ?? null);
        $this->validateEndpoint($validated['verification_url'] ?? null);

        $code = Str::slug($validated['code']);
        if ($code === '' || PaymentGateway::where('code', $code)->exists()) {
            throw ValidationException::withMessages([
                'code' => 'Kode gateway tidak valid atau sudah digunakan.',
            ]);
        }

        $driver = $validated['driver'] ?? 'generic';
        $config = [
            'api_key'       => $validated['api_key'] ?? null,
            'server_key'    => $validated['server_key'] ?? null,
            'client_key'    => $validated['client_key'] ?? null,
            'webhook_token' => $validated['webhook_token'] ?? null,
            'endpoint_url'  => $validated['endpoint_url'] ?? null,
            'verification_url' => $validated['verification_url'] ?? null,
            'is_production' => $request->boolean('is_production'),
        ];
        if (!PaymentGateway::canEncryptSensitiveConfig($config)) {
            throw ValidationException::withMessages([
                'api_key' => 'APP_KEY tidak tersedia atau tidak valid. Konfigurasi kredensial tidak disimpan sampai enkripsi aplikasi siap.',
            ]);
        }
        $config = PaymentGateway::encryptSensitiveConfig($config);

        $gateway = new PaymentGateway([
            'name'        => $validated['name'],
            'code'        => $code,
            'driver'      => $driver,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
            'config'      => $config,
        ]);

        if ($gateway->is_active && !$gateway->isOperationallyVerified()) {
            throw ValidationException::withMessages([
                'is_active' => 'Gateway tidak dapat diaktifkan sebelum konfigurasi dan verifikasi konektivitas lengkap.',
            ]);
        }

        $gateway->save();

        return redirect()
            ->route('admin.payment.gateway.index')
            ->with('success', "Payment Gateway \"{$validated['name']}\" berhasil ditambahkan.");
    }

    /**
     * Update konfigurasi & API Key gateway langsung dari Admin UI.
     */
    public function update(Request $request, PaymentGateway $gateway)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string|max:255',
            'api_key'       => 'nullable|string|max:255',
            'server_key'    => 'nullable|string|max:255',
            'client_key'    => 'nullable|string|max:255',
            'webhook_token' => 'nullable|string|max:255',
            'endpoint_url'  => 'nullable|url|max:255',
            'verification_url' => 'nullable|url|max:255',
            'is_production' => 'nullable|boolean',
            'is_active'     => 'nullable|boolean',
            'clear_credentials' => 'nullable|boolean',
        ]);

        $this->validateEndpoint($validated['endpoint_url'] ?? null);
        $this->validateEndpoint($validated['verification_url'] ?? null);

        $existingConfig = $gateway->config ?? [];
        $configurationChanged = $request->boolean('clear_credentials') ||
            $request->has('is_production') &&
            $request->boolean('is_production') !== (bool) ($existingConfig['is_production'] ?? false);

        foreach (['api_key', 'server_key', 'client_key', 'webhook_token'] as $key) {
            if ($request->filled($key)) {
                $configurationChanged = true;
                $existingConfig[$key] = $validated[$key];
            }
        }
        foreach (['endpoint_url', 'verification_url'] as $key) {
            if ($request->filled($key)) {
                $configurationChanged = $configurationChanged
                    || $request->input($key) !== ($existingConfig[$key] ?? null);
                $existingConfig[$key] = $validated[$key];
            }
        }
        if (PaymentGateway::hasPlaintextSensitiveConfig($existingConfig)) {
            $configurationChanged = true;
        }

        if ($configurationChanged && $gateway->hasUnresolvedTransactions()) {
            throw ValidationException::withMessages([
                'api_key' => 'Konfigurasi tidak dapat diubah selama ada pembayaran yang belum terminal. Selesaikan atau biarkan transaksi lama mencapai status terminal terlebih dahulu.',
            ]);
        }

        if ($request->boolean('clear_credentials')) {
            foreach (['api_key', 'server_key', 'client_key', 'webhook_token', 'endpoint_url', 'verification_url'] as $key) {
                unset($existingConfig[$key]);
            }
        }
        if ($configurationChanged) {
            unset($existingConfig['connectivity_verified_at']);
            $existingConfig['connectivity_verification_reason'] = 'Konfigurasi berubah; verifikasi konektivitas ulang diperlukan.';
        }
        $existingConfig['is_production'] = $request->boolean('is_production');
        if (!PaymentGateway::canEncryptSensitiveConfig($existingConfig)) {
            throw ValidationException::withMessages([
                'api_key' => 'APP_KEY tidak tersedia atau tidak valid. Kredensial gateway tidak dapat disimpan dengan aman.',
            ]);
        }
        $newConfig = PaymentGateway::encryptSensitiveConfig($existingConfig);

        $gateway->fill([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $configurationChanged ? false : $request->boolean('is_active'),
            'config'      => $newConfig,
        ]);

        if ($gateway->is_active && !$gateway->isOperationallyVerified()) {
            throw ValidationException::withMessages([
                'is_active' => 'Gateway tidak dapat diaktifkan sebelum konfigurasi dan verifikasi konektivitas lengkap.',
            ]);
        }

        $gateway->save();

        return redirect()
            ->route('admin.payment.gateway.index')
            ->with('success', $configurationChanged
                ? "Konfigurasi Gateway \"{$gateway->name}\" tersimpan dan gateway dinonaktifkan sampai konektivitas diverifikasi ulang."
                : "Konfigurasi Gateway \"{$gateway->name}\" berhasil diperbarui.");
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
        $isActive = !$gateway->is_active;
        if ($isActive && !$gateway->isOperationallyVerified()) {
            return back()->with('error', "Gateway {$gateway->name} belum memiliki konfigurasi lengkap dan verifikasi konektivitas.");
        }

        $gateway->update(['is_active' => $isActive]);

        $status = $gateway->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Gateway {$gateway->name} berhasil {$status}.");
    }

    public function verifyConnectivity(
        PaymentGateway $gateway,
        PaymentGatewayConnectivityVerifier $verifier
    ) {
        $result = $verifier->verify($gateway);
        $config = $gateway->config ?? [];

        if ($result['success']) {
            $config['connectivity_verified_at'] = now()->toIso8601String();
            unset($config['connectivity_verification_reason']);
        } else {
            unset($config['connectivity_verified_at']);
            $config['connectivity_verification_reason'] = $result['message'];
        }

        if (!PaymentGateway::canEncryptSensitiveConfig($config)) {
            return back()->with('error', 'APP_KEY tidak tersedia atau tidak valid; status verifikasi tidak dapat disimpan dengan aman.');
        }

        $gateway->update(['config' => $config]);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function validateEndpoint(?string $endpoint): void
    {
        if ($endpoint === null || $endpoint === '') {
            return;
        }

        try {
            PublicGatewayEndpoint::assertSafeUrl($endpoint);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'endpoint_url' => $exception->getMessage(),
            ]);
        }
    }
}
