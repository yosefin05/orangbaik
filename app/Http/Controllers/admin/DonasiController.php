<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonasiController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Donasi::with(['campaign', 'user', 'pembayaran']);

        // Filter by campaign
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        // Filter by status pembayaran
        if ($request->filled('status')) {
            $query->whereHas('pembayaran', function ($q) use ($request) {
                $q->where('transaction_status', $request->status);
            });
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by nama donatur, email, atau nomor hp
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_donatur', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('no_hp', 'LIKE', "%{$search}%");
            });
        }

        $donasi = $query->latest()->paginate(20);
        $campaigns = Campaign::where('is_active', true)->get();
        $statuses = ['pending', 'settlement', 'expire'];

        return view('admin.donasi.index', compact('donasi', 'campaigns', 'statuses'));
    }

    
    public function create()
    {
        $campaigns = Campaign::where('is_active', true)->get();
        $users = User::where('role', 'user')->get();
        return view('admin.donasi.create', compact('campaigns', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaign,id',
            'user_id' => 'nullable|exists:users,id',
            'nama_donatur' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'no_hp' => 'nullable|string|max:20',
            'nominal' => 'required|numeric|min:1',
            'pesan_doa' => 'nullable|string',
            'is_anonim' => 'boolean',
            'transaction_status' => 'required|in:pending,settlement,expire',
            'payment_type' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $donasi = Donasi::create([
                'campaign_id' => $request->campaign_id,
                'user_id' => $request->user_id,
                'nama_donatur' => $request->nama_donatur,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'nominal' => $request->nominal,
                'pesan_doa' => $request->pesan_doa,
                'is_anonim' => $request->boolean('is_anonim'),
            ]);

            $donasi->pembayaran()->create([
                'order_id' => 'ADMIN-' . time() . '-' . $donasi->id,
                'transaction_status' => $request->transaction_status,
                'payment_type' => $request->payment_type ?? 'manual',
                'paid_at' => $request->paid_at ?? ($request->transaction_status === 'settlement' ? now() : null),
                'transaction_id' => 'MANUAL-' . time(),
            ]);

            DB::commit();

            return redirect()->route('admin.donasi.index')
                ->with('success', 'Donasi berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan donasi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $donasi = Donasi::with(['campaign', 'user', 'pembayaran.paymentChannel.gateway'])->findOrFail($id);
        return view('admin.donasi.show', compact('donasi'));
    }

    /**
     * Verifikasi (approve) pembayaran transfer manual
     */
    public function approveManual($id)
    {
        $donasi = Donasi::with('pembayaran')->findOrFail($id);

        if (!$donasi->pembayaran) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $donasi->pembayaran->update([
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
            'rejection_reason'   => null,
        ]);

        return back()->with('success', 'Pembayaran manual donasi #' . $donasi->id . ' berhasil diverifikasi (Settlement).');
    }

    /**
     * Tolak (reject) pembayaran transfer manual
     */
    public function rejectManual(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        $donasi = Donasi::with('pembayaran')->findOrFail($id);

        if (!$donasi->pembayaran) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $donasi->pembayaran->update([
            'transaction_status' => 'failed',
            'rejection_reason'   => $request->rejection_reason ?? 'Bukti transfer tidak valid atau dana belum masuk.',
        ]);

        return back()->with('warning', 'Pembayaran manual donasi #' . $donasi->id . ' telah ditolak.');
    }

    public function edit($id)
    {
        $donasi = Donasi::with('pembayaran')->findOrFail($id);
        $campaigns = Campaign::where('is_active', true)->get();
        $users = User::where('role', 'user')->get();
        $statuses = ['pending', 'settlement', 'expire'];

        return view('admin.donasi.edit', compact('donasi', 'campaigns', 'users', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $donasi = Donasi::with('pembayaran')->findOrFail($id);

        $request->validate([
            'campaign_id' => 'required|exists:campaign,id',
            'user_id' => 'nullable|exists:users,id',
            'nama_donatur' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'no_hp' => 'nullable|string|max:20',
            'nominal' => 'required|numeric|min:1',
            'pesan_doa' => 'nullable|string',
            'is_anonim' => 'boolean',
            'transaction_status' => 'required|in:pending,settlement,expire',
            'payment_type' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $donasi->update([
                'campaign_id' => $request->campaign_id,
                'user_id' => $request->user_id,
                'nama_donatur' => $request->nama_donatur,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'nominal' => $request->nominal,
                'pesan_doa' => $request->pesan_doa,
                'is_anonim' => $request->boolean('is_anonim'),
            ]);

            $donasi->pembayaran->update([
                'transaction_status' => $request->transaction_status,
                'payment_type' => $request->payment_type ?? $donasi->pembayaran->payment_type,
                'paid_at' => $request->paid_at ?? ($request->transaction_status === 'settlement' ? now() : null),
            ]);

            DB::commit();

            return redirect()->route('admin.donasi.index')
                ->with('success', 'Donasi berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate donasi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $donasi = Donasi::findOrFail($id);
        $donasi->delete();

        return redirect()->route('admin.donasi.index')
            ->with('success', 'Donasi berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $query = Donasi::with(['campaign', 'user', 'pembayaran']);

        // Apply filters
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }
        if ($request->filled('status')) {
            $query->whereHas('pembayaran', function ($q) use ($request) {
                $q->where('transaction_status', $request->status);
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $donasi = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="donasi_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($donasi) {
            $file = fopen('php://output', 'w');

            // Header CSV
            fputcsv($file, [
                'ID',
                'Campaign',
                'Donatur',
                'Email',
                'No HP',
                'Nominal',
                'Status Pembayaran',
                'Metode Pembayaran',
                'Tanggal Donasi',
                'Tanggal Bayar'
            ]);

            // Data CSV
            foreach ($donasi as $d) {
                fputcsv($file, [
                    $d->id,
                    $d->campaign->judul ?? '-',
                    $d->is_anonim ? 'Anonim' : $d->nama_donatur,
                    $d->email ?? '-',
                    $d->no_hp ?? '-',
                    $d->nominal,
                    $d->pembayaran->transaction_status ?? '-',
                    $d->pembayaran->payment_type ?? '-',
                    $d->created_at,
                    $d->pembayaran->paid_at ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}