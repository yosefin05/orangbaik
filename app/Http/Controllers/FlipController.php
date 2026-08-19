<?php

namespace App\Http\Controllers;

use App\Services\FlipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlipController extends Controller
{
    protected FlipService $flipService;

    public function __construct(FlipService $flipService)
    {
        $this->flipService = $flipService;
    }

    /**
     * Endpoint webhook dari Flip.
     * Flip mengirim POST ke URL ini setelah status VA berubah.
     *
     * Validasi:
     * 1. Header X-CALLBACK-TOKEN harus cocok dengan FLIP_WEBHOOK_TOKEN di .env
     * 2. Signature/token diverifikasi di FlipService
     *
     * URL: POST /payment/flip/webhook
     */
    public function notification(Request $request)
    {
        try {
            $token   = $request->header('X-CALLBACK-TOKEN', '');
            $payload = $request->all();

            Log::info('Flip webhook received', [
                'token_present' => !empty($token),
                'payload_keys'  => array_keys($payload),
            ]);

            $result = $this->flipService->handleWebhook($token, $payload);

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
            Log::error('Flip webhook error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }
}
