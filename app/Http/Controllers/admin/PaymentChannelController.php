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
        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $channels = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->orderBy('sort_order')
            ->get();

        $gateways = collect([$manualGateway]);

        return view('admin.payment.channel.index', compact('channels', 'gateways'));
    }

    /**
     * Simpan seluruh baris payment channel sekaligus (Batch Save).
     */
    public function batchUpdate(Request $request)
    {
        $request->validate([
            'channels' => 'nullable|array',
            'channels.*.id' => 'nullable|integer|exists:payment_channels,id',

            'channels.*.name' => 'required|string|max:100',
            'channels.*.channel_code' => 'required|string|max:50',
            'channels.*.account_name' => 'nullable|string|max:100',
            'channels.*.account_number' => 'nullable|string|max:50',
            'channels.*.payment_type' => 'required|in:instant,va,transfer',
            'channels.*.is_active' => 'nullable|boolean',
            'deleted_ids' => 'nullable|array',
            'deleted_ids.*' => 'integer|exists:payment_channels,id',
        ]);

        try {
            $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();

            DB::transaction(function () use ($request, $manualGateway): void {
                foreach ($request->input('deleted_ids', []) as $deletedId) {
                    $channel = PaymentChannel::query()->lockForUpdate()->find($deletedId);
                    if (!$channel || $channel->payment_gateway_id !== $manualGateway->id) {
                        throw ValidationException::withMessages(['deleted_ids' => 'Hanya rekening transfer manual yang dapat dikelola dari halaman ini.']);
                    }

                    if ($channel->hasTransactions()) {
                        $channel->update(['is_active' => false]);
                    } else {
                        $channel->delete();
                    }
                }

                foreach ($request->input('channels', []) as $index => $item) {
                    if (blank($item['account_name'] ?? null) || blank($item['account_number'] ?? null)) {
                        throw ValidationException::withMessages([
                            "channels.{$index}.account_number" => 'Nama pemilik dan nomor rekening wajib diisi untuk transfer manual.',
                        ]);
                    }

                    $payload = [
                        'payment_gateway_id' => $manualGateway->id,
                        'name' => trim($item['name']),
                        'channel_code' => trim($item['channel_code']),
                        'account_name' => $item['account_name'] ?? null,
                        'account_number' => $item['account_number'] ?? null,
                        'payment_type' => 'transfer',
                        'is_active' => filter_var($item['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'sort_order' => $index + 1,
                    ];

                    $channel = !empty($item['id'])
                        ? PaymentChannel::query()->lockForUpdate()->findOrFail($item['id'])
                        : new PaymentChannel();

                    if ($channel->exists && $channel->payment_gateway_id !== $manualGateway->id) {
                        throw ValidationException::withMessages([
                            "channels.{$index}.id" => 'Channel provider tidak dapat diedit dari pengelolaan rekening manual.',
                        ]);
                    }

                    if ($channel->exists && $channel->hasTransactions()) {
                        foreach (['payment_gateway_id', 'channel_code', 'account_name', 'account_number', 'payment_type'] as $immutableField) {
                            if ((string) $channel->{$immutableField} !== (string) $payload[$immutableField]) {
                                throw ValidationException::withMessages([
                                    "channels.{$index}.{$immutableField}" => 'Channel ini sudah memiliki riwayat transaksi. Nonaktifkan lalu buat channel baru agar riwayat pembayaran tetap akurat.',
                                ]);
                            }
                        }
                    }

                    $channel->fill($payload);
                    $channel->save();
                }
            });

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Konfigurasi channel berhasil disimpan.',
                ]);
            }

            return redirect()
                ->route('admin.payment.channel.index')
                ->with('success', 'Rekening transfer manual berhasil disimpan.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi gagal disimpan. Periksa kembali data dan coba lagi.',
                ], 500);
            }

            return back()->withInput()->with('error', 'Konfigurasi gagal disimpan. Tidak ada perubahan yang diterapkan.');
        }
    }

    /**
     * Form tambah channel baru (opsional).
     */
    public function create()
    {
        $gateways = collect([PaymentGateway::where('code', 'manual')->firstOrFail()]);
        return view('admin.payment.channel.create', compact('gateways'));
    }

    /**
     * Simpan channel baru satuan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'name'               => 'required|string|max:100',
            'channel_code'       => 'required|string|max:50',
            'account_name'       => 'nullable|string|max:100',
            'account_number'     => 'nullable|string|max:50',
            'payment_type'       => 'required|in:instant,va,transfer',
            'sort_order'         => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $gateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        if ((int) $request->payment_gateway_id !== $gateway->id) {
            throw ValidationException::withMessages(['payment_gateway_id' => 'Halaman ini khusus untuk rekening transfer manual.']);
        }
        if (!$gateway->is_active) {
            throw ValidationException::withMessages(['payment_gateway_id' => 'Gateway yang dipilih sedang nonaktif.']);
        }
        if ($gateway->code === 'manual' && (blank($request->account_name) || blank($request->account_number))) {
            throw ValidationException::withMessages(['account_number' => 'Nama pemilik dan nomor rekening wajib diisi untuk transfer manual.']);
        }

        $maxSort = PaymentChannel::max('sort_order') ?? 0;

        PaymentChannel::create([
            'payment_gateway_id' => $gateway->id,
            'name' => $request->name,
            'channel_code' => $request->channel_code,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'payment_type' => $gateway->code === 'manual' ? 'transfer' : $request->payment_type,
            'sort_order' => $request->sort_order ?? ($maxSort + 1),
            'is_active' => ($gateway->code === 'manual' || $gateway->isConfigured())
                ? $request->boolean('is_active', true)
                : false,
        ]);

        return redirect()
            ->route('admin.payment.channel.index')
            ->with('success', 'Payment channel berhasil ditambahkan.');
    }

    /**
     * Form edit channel.
     */
    public function edit(PaymentChannel $channel)
    {
        abort_unless($channel->gateway?->code === 'manual', 404);
        $gateways = collect([$channel->gateway]);
        return view('admin.payment.channel.edit', compact('channel', 'gateways'));
    }

    /**
     * Update channel satuan.
     */
    public function update(Request $request, PaymentChannel $channel)
    {
        $request->validate([
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'name'               => 'required|string|max:100',
            'channel_code'       => 'required|string|max:50',
            'account_name'       => 'nullable|string|max:100',
            'account_number'     => 'nullable|string|max:50',
            'payment_type'       => 'required|in:instant,va,transfer',
            'sort_order'         => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $gateway = PaymentGateway::findOrFail($request->payment_gateway_id);
        if (!$gateway->is_active) {
            throw ValidationException::withMessages(['payment_gateway_id' => 'Gateway yang dipilih sedang nonaktif.']);
        }
        if ($gateway->code === 'manual' && (blank($request->account_name) || blank($request->account_number))) {
            throw ValidationException::withMessages(['account_number' => 'Nama pemilik dan nomor rekening wajib diisi untuk transfer manual.']);
        }

        $sensitiveChanges = [
            'payment_gateway_id' => $gateway->id,
            'channel_code' => $request->channel_code,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'payment_type' => $gateway->code === 'manual' ? 'transfer' : $request->payment_type,
        ];

        if ($channel->hasTransactions()) {
            foreach ($sensitiveChanges as $field => $value) {
                if ((string) $channel->{$field} !== (string) $value) {
                    throw ValidationException::withMessages([
                        $field => 'Channel ini sudah memiliki riwayat transaksi. Nonaktifkan lalu buat channel baru untuk mengganti detail rekening/provider.',
                    ]);
                }
            }
        }

        $channel->update(array_merge($sensitiveChanges, [
            'name' => $request->name,
            'sort_order' => $request->sort_order ?? $channel->sort_order,
            'is_active' => ($gateway->code === 'manual' || $gateway->isConfigured())
                ? $request->boolean('is_active', $channel->is_active)
                : false,
        ]));

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
        if (!$channel->is_active && (!$channel->gateway?->is_active || ($channel->gateway->code !== 'manual' && !$channel->gateway->isConfigured()))) {
            return back()->with('error', 'Gateway belum aktif atau belum memiliki kredensial wajib.');
        }

        $channel->update(['is_active' => !$channel->is_active]);
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
}
