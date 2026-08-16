<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response('Invalid payload', 400);
        } catch(SignatureVerificationException $e) {
            // Invalid signature
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                
                $orderId = $session->metadata->order_id ?? null;
                
                if ($orderId) {
                    $order = Order::find($orderId);
                    if ($order) {
                        $order->payment_status = 'Paid';
                        $order->save();
                        Log::info("Order {$order->order_number} marked as Paid via Stripe Webhook.");
                    }
                }
                break;
            default:
                Log::info('Received unknown event type ' . $event->type);
        }

        return response('Webhook handled', 200);
    }
}
