<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentChannelController extends Controller
{
    /**
     * Tampilkan semua payment channel dalam format inline row list (sesuai referensi OrangBaik sebelumnya).
     */
    public function index()
    {
        $channels = PaymentChannel::with('gateway')
            ->withCount('pembayarans')
            ->orderBy('sort_order')
            ->get();

        $gateways = PaymentGateway::all();

        return view('admin.payment.channel.index', compact('channels', 'gateways'));
    }

    /**
     * Simpan seluruh baris payment channel sekaligus (Batch Save).
     */
    public function batchUpdate(Request $request)
    {
        $validated = $request->validate([
            'channels'                      => ['nullable', 'array'],
            'channels.*.id'                 => ['nullable', 'integer', 'exists:payment_channels,id'],
            'channels.*.name'               => ['required', 'string', 'max:100'],
            'channels.*.channel_code'       => ['required', 'string', 'max:50'],
            'channels.*.payment_gateway_id' => ['required', 'integer', 'exists:payment_gateways,id'],
            'channels.*.account_name'       => ['nullable', 'string', 'max:100'],
            'channels.*.account_number'     => ['nullable', 'string', 'max:50'],
            'channels.*.instructions'       => ['nullable', 'string', 'max:5000'],
            'channels.*.payment_type'       => ['required', 'in:instant,va,transfer'],
            'channels.*.is_active'          => ['required', 'boolean'],
            'deleted_ids'                   => ['nullable', 'array'],
            'deleted_ids.*'                 => ['integer', 'exists:payment_channels,id'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['deleted_ids'] ?? [] as $deletedId) {
                $channel = PaymentChannel::whereKey($deletedId)->lockForUpdate()->first();
                if (!$channel) {
                    continue;
                }

                if ($channel->hasTransactions()) {
                    $channel->update(['is_active' => false]);
                    continue;
                }

                $channel->gateway?->setChannelInstructions($channel, null);
                $channel->delete();
            }

            foreach ($validated['channels'] ?? [] as $index => $item) {
                $channel = !empty($item['id'])
                    ? PaymentChannel::whereKey($item['id'])->lockForUpdate()->firstOrFail()
                    : new PaymentChannel();
                $oldGateway = $channel->exists ? $channel->gateway : null;
                $gateway = PaymentGateway::findOrFail($item['payment_gateway_id']);

                $payload = [
                    'payment_gateway_id' => $gateway->id,
                    'name'               => $item['name'],
                    'channel_code'       => $item['channel_code'],
                    'account_name'       => $item['account_name'] ?? null,
                    'account_number'     => $item['account_number'] ?? null,
                    'payment_type'       => $item['payment_type'],
                    'sort_order'         => $index + 1,
                    'is_active'          => (bool) $item['is_active'],
                ];

                $this->ensureChannelCanBeActivated($gateway, $payload);
                $this->ensureExistingTransactionsKeepTheirPaymentDetails($channel, $payload);

                $channel->fill($payload);
                $channel->save();

                if ($oldGateway && $oldGateway->id !== $gateway->id) {
                    $oldGateway->setChannelInstructions($channel, null);
                }
                $gateway->setChannelInstructions($channel, $item['instructions'] ?? null);
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi payment channel berhasil disimpan.',
            ]);
        }

        return redirect()
            ->route('admin.payment.channel.index')
            ->with('success', 'Konfigurasi payment channel berhasil disimpan.');
    }

    /**
     * Form tambah channel baru (opsional).
     */
    public function create()
    {
        $gateways = PaymentGateway::orderBy('name')->get();
        return view('admin.payment.channel.create', compact('gateways'));
    }

    /**
     * Simpan channel baru satuan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'name'               => 'required|string|max:100',
            'channel_code'       => 'required|string|max:50',
            'account_name'       => 'nullable|string|max:100',
            'account_number'     => 'nullable|string|max:50',
            'instructions'       => 'nullable|string|max:5000',
            'payment_type'       => 'required|in:instant,va,transfer',
            'sort_order'         => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $maxSort = PaymentChannel::max('sort_order') ?? 0;
        $gateway = PaymentGateway::findOrFail($validated['payment_gateway_id']);
        $payload = [
            'payment_gateway_id' => $gateway->id,
            'name'               => $validated['name'],
            'channel_code'       => $validated['channel_code'],
            'account_name'       => $validated['account_name'] ?? null,
            'account_number'     => $validated['account_number'] ?? null,
            'payment_type'       => $validated['payment_type'],
            'sort_order'         => $validated['sort_order'] ?? ($maxSort + 1),
            'is_active'          => $request->boolean('is_active'),
        ];

        $this->ensureChannelCanBeActivated($gateway, $payload);

        DB::transaction(function () use ($gateway, $payload, $validated): void {
            $channel = PaymentChannel::create($payload);
            $gateway->setChannelInstructions($channel, $validated['instructions'] ?? null);
        });

        return redirect()
            ->route('admin.payment.channel.index')
            ->with('success', 'Payment channel berhasil ditambahkan.');
    }

    /**
     * Form edit channel.
     */
    public function edit(PaymentChannel $channel)
    {
        $gateways = PaymentGateway::orderBy('name')->get();
        return view('admin.payment.channel.edit', compact('channel', 'gateways'));
    }

    /**
     * Update channel satuan.
     */
    public function update(Request $request, PaymentChannel $channel)
    {
        $validated = $request->validate([
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'name'               => 'required|string|max:100',
            'channel_code'       => 'required|string|max:50',
            'account_name'       => 'nullable|string|max:100',
            'account_number'     => 'nullable|string|max:50',
            'instructions'       => 'nullable|string|max:5000',
            'payment_type'       => 'required|in:instant,va,transfer',
            'sort_order'         => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $gateway = PaymentGateway::findOrFail($validated['payment_gateway_id']);
        $payload = [
            'payment_gateway_id' => $gateway->id,
            'name'               => $validated['name'],
            'channel_code'       => $validated['channel_code'],
            'account_name'       => $validated['account_name'] ?? null,
            'account_number'     => $validated['account_number'] ?? null,
            'payment_type'       => $validated['payment_type'],
            'sort_order'         => $validated['sort_order'] ?? $channel->sort_order,
            'is_active'          => $request->boolean('is_active'),
        ];

        $this->ensureChannelCanBeActivated($gateway, $payload);
        $this->ensureExistingTransactionsKeepTheirPaymentDetails($channel, $payload);

        DB::transaction(function () use ($channel, $gateway, $payload, $validated): void {
            $oldGateway = $channel->gateway;
            $channel->update($payload);

            if ($oldGateway && $oldGateway->id !== $gateway->id) {
                $oldGateway->setChannelInstructions($channel, null);
            }
            $gateway->setChannelInstructions($channel, $validated['instructions'] ?? null);
        });

        return redirect()
            ->route('admin.payment.channel.index')
            ->with('success', 'Payment channel berhasil diperbarui.');
    }

    /**
     * Hapus channel.
     */
    public function destroy(PaymentChannel $channel)
    {
        if ($channel->hasTransactions()) {
            $channel->update(['is_active' => false]);
            return redirect()
                ->route('admin.payment.channel.index')
                ->with('warning', "Channel \"{$channel->name}\" sudah pernah digunakan. Dinonaktifkan.");
        }

        $channel->gateway?->setChannelInstructions($channel, null);
        $channel->delete();

        return redirect()
            ->route('admin.payment.channel.index')
            ->with('success', "Payment channel \"{$channel->name}\" berhasil dihapus.");
    }

    /**
     * Toggle status aktif/nonaktif channel.
     */
    public function toggleActive(PaymentChannel $channel)
    {
        $isActive = !$channel->is_active;
        if ($isActive) {
            $attributes = $channel->getAttributes();
            $attributes['is_active'] = true;
            $this->ensureChannelCanBeActivated($channel->gateway, $attributes);
        }
        $channel->update(['is_active' => $isActive]);
        $status = $channel->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Channel \"{$channel->name}\" berhasil {$status}.");
    }

    /**
     * Update urutan channel.
     */
    public function updateSort(Request $request)
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:payment_channels,id',
        ]);

        foreach ($request->order as $sortOrder => $channelId) {
            PaymentChannel::where('id', $channelId)->update(['sort_order' => $sortOrder + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Urutan berhasil disimpan.']);
    }

    private function ensureChannelCanBeActivated(PaymentGateway $gateway, array $channel): void
    {
        if (empty($channel['is_active'])) {
            return;
        }

        if ($gateway->code === 'manual') {
            $hasManualDetails = ($channel['payment_type'] ?? null) === 'transfer'
                && filled($channel['name'] ?? null)
                && filled($channel['account_name'] ?? null)
                && filled($channel['account_number'] ?? null);

            if (!$hasManualDetails) {
                throw ValidationException::withMessages([
                    'account_number' => 'Channel transfer manual memerlukan nama bank, nama pemilik, dan nomor rekening.',
                ]);
            }

            return;
        }
    }

    private function ensureExistingTransactionsKeepTheirPaymentDetails(PaymentChannel $channel, array $payload): void
    {
        if (!$channel->exists || !$channel->hasTransactions()) {
            return;
        }

        foreach ([
            'payment_gateway_id',
            'name',
            'channel_code',
            'account_name',
            'account_number',
            'payment_type',
        ] as $field) {
            if (($channel->{$field} ?? null) != ($payload[$field] ?? null)) {
                throw ValidationException::withMessages([
                    'channels' => "Channel \"{$channel->name}\" sudah digunakan. Buat channel baru agar transaksi berjalan tetap memakai detail pembayaran awal.",
                ]);
            }
        }
    }
}
