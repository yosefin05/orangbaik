<?php

namespace App\Http\Controllers;

use App\Services\IpaymuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IpaymuController extends Controller
{
    public function __construct(protected IpaymuService $ipaymuService) {}

    public function notification(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('iPaymu webhook received', $payload);

            $result = $this->ipaymuService->handleWebhook($payload);

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Notification rejected'], 400);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('iPaymu webhook error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }
}
