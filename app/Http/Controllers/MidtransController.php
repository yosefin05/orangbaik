<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function notification(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Midtrans webhook received', $payload);

            $result = $this->midtransService->handleWebhook($payload);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification rejected',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification processed',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Midtrans webhook error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }
}