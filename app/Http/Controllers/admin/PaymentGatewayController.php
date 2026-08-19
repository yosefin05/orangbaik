<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    /**
     * Tampilkan daftar semua payment gateway.
     */
    public function index()
    {
        $gateways = PaymentGateway::withCount('channels')->get();

        return view('admin.payment.gateway.index', compact('gateways'));
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
