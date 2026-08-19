<?php

namespace App\Http\Controllers;

use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TripayController extends Controller
{
    public function __construct(protected TripayService $tripayService) {}

    public function notification(Request $request)
    {
        try {
            $signature = $request->server('HTTP_X_CALLBACK_SIGNATURE', '');
            $rawContent = $request->getContent();
            $data = json_decode($rawContent, true) ?? [];

            Log::info('Tripay webhook received', ['event' => $request->server('HTTP_X_CALLBACK_EVENT')]);

            $result = $this->tripayService->handleWebhook($signature, $rawContent, $data);

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Invalid signature or payload'], 400);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Tripay webhook error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }
}
