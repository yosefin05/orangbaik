<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use App\Services\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewayManager $manager
    ) {}

    /**
     * Endpoint dinamis webhook untuk semua payment gateway:
     * POST /payment/{gateway}/webhook
     */
    public function handle(Request $request, string $gateway)
    {
        try {
            $gatewayModel = PaymentGateway::where('code', $gateway)->first();

            if (!$gatewayModel) {
                Log::warning("Webhook received for unknown gateway: {$gateway}");
                return response()->json(['success' => false, 'message' => 'Gateway not found'], 404);
            }

            Log::info("Payment webhook received for gateway: {$gateway}", [
                'headers' => $request->headers->all(),
                'body'    => $request->all(),
            ]);

            $driver = $this->manager->driver($gatewayModel);
            $result = $driver->handleWebhook($request);

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Webhook rejected or unhandled'], 400);
            }

            return response()->json(['success' => true, 'message' => 'Webhook processed successfully'], 200);

        } catch (\Exception $e) {
            Log::error("Payment webhook error for gateway: {$gateway}", [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
