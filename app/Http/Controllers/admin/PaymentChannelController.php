<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentChannelController extends Controller
{
    /**
     * Tampilkan semua payment channel dalam format inline row list (sesuai referensi OrangBaik sebelumnya).
     */
    public function index()
    {
        $channels = PaymentChannel::with('gateway')
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
        $channelsData = $request->input('channels', []);
        $deletedIds   = $request->input('deleted_ids', []);

        DB::beginTransaction();
        try {
            // 1. Proses penghapusan baris yang dihapus oleh admin
            if (!empty($deletedIds)) {
                foreach ($deletedIds as $delId) {
                    $channel = PaymentChannel::find($delId);
                    if ($channel) {
                        if ($channel->hasTransactions()) {
                            // Soft-disable jika sudah ada transaksi
                            $channel->update(['is_active' => false]);
                        } else {
                            $channel->delete();
                        }
                    }
                }
            }

            // 2. Proses update / insert setiap baris
            foreach ($channelsData as $index => $item) {
                if (empty($item['name']) || empty($item['channel_code'])) {
                    continue; // Skip baris kosong
                }

                $channelId = $item['id'] ?? null;
                $payload = [
                    'payment_gateway_id' => $item['payment_gateway_id'],
                    'name'               => $item['name'],
                    'channel_code'       => $item['channel_code'],
                    'account_name'       => $item['account_name'] ?? null,
                    'account_number'     => $item['account_number'] ?? null,
                    'payment_type'       => $item['payment_type'] ?? 'instant',
                    'sort_order'         => $index + 1,
                    'is_active'          => isset($item['is_active']) ? (bool)$item['is_active'] : true,
                ];

                if ($channelId && PaymentChannel::where('id', $channelId)->exists()) {
                    PaymentChannel::where('id', $channelId)->update($payload);
                } else {
                    PaymentChannel::create($payload);
                }
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Konfigurasi payment channel berhasil disimpan!',
                ]);
            }

            return redirect()
                ->route('admin.payment.channel.index')
                ->with('success', 'Konfigurasi payment channel berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Form tambah channel baru (opsional).
     */
    public function create()
    {
        $gateways = PaymentGateway::where('is_active', true)->get();
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

        $maxSort = PaymentChannel::max('sort_order') ?? 0;

        PaymentChannel::create([
            'payment_gateway_id' => $request->payment_gateway_id,
            'name'               => $request->name,
            'channel_code'       => $request->channel_code,
            'account_name'       => $request->account_name,
            'account_number'     => $request->account_number,
            'payment_type'       => $request->payment_type,
            'sort_order'         => $request->sort_order ?? ($maxSort + 1),
            'is_active'          => $request->boolean('is_active', true),
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
        $gateways = PaymentGateway::where('is_active', true)->get();
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

        $channel->update([
            'payment_gateway_id' => $request->payment_gateway_id,
            'name'               => $request->name,
            'channel_code'       => $request->channel_code,
            'account_name'       => $request->account_name,
            'account_number'     => $request->account_number,
            'payment_type'       => $request->payment_type,
            'sort_order'         => $request->sort_order ?? $channel->sort_order,
            'is_active'          => $request->boolean('is_active', $channel->is_active),
        ]);

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
