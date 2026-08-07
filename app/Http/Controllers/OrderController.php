<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\PromotionCalculator;
use App\Support\StaffNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $cartDataRaw = $request->input('cart_data');
        $cartItems = [];
        if ($cartDataRaw) {
            $decoded = json_decode($cartDataRaw, true);
            $cartItems = is_array($decoded) ? $decoded : [];
        }

        if (empty($cartItems)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $subtotal = 0;
        $formattedItems = [];

        foreach ($cartItems as $item) {
            $itemPrice = (int) ($item['price'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $subtotal += $itemPrice * $qty;

            $name = (string) ($item['name'] ?? 'Item');
            if (! empty($item['size']) || ! empty($item['crust'])) {
                $customizations = [];
                if (! empty($item['size'])) {
                    $customizations[] = $item['size'];
                }
                if (! empty($item['crust'])) {
                    $customizations[] = $item['crust'];
                }
                if (! empty($item['toppings']) && is_array($item['toppings'])) {
                    $toppingNames = array_map(fn ($t) => $t['name'] ?? 'Topping', $item['toppings']);
                    $customizations[] = implode(', ', $toppingNames);
                }
                $name .= ' ['.implode(' | ', $customizations).']';
            }

            $formattedItems[] = [
                'name' => $name,
                'qty' => $qty,
                'price' => $itemPrice,
            ];
        }

        $quote = PromotionCalculator::quote(
            $subtotal,
            $request->input('promo_code'),
            $user
        );

        // If a code was typed but not successfully applied, block checkout.
        $requestedCode = trim((string) $request->input('promo_code', ''));
        if ($requestedCode !== '' && empty($quote['promo_code'])) {
            return back()->with('error', $quote['message'] ?? 'Invalid promo code.');
        }

        $orderNumber = 'PF-'.mt_rand(10000, 99999);

        $addr = $user->defaultAddress();
        $address = $addr ? $addr->address_line.', '.$addr->city : 'Pickup';
        $instructions = $user->preferences['delivery_notes'] ?? '';

        Order::create([
            'user_id' => $user->_id,
            'order_number' => $orderNumber,
            'items' => $formattedItems,
            'subtotal' => $quote['subtotal'],
            'delivery_fee' => $quote['delivery_fee'],
            'discount' => $quote['discount'],
            'total' => $quote['total'],
            'status' => 'pending',
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Pending',
            'promo_code' => $quote['promo_code'],
            'promotion_id' => $quote['promotion_id'],
            'promotion_title' => $quote['promotion_title'],
            'delivery_address' => $address,
            'delivery_city' => $addr?->city,
            'delivery_landmark' => $addr?->landmark,
            'delivery_instructions' => $instructions,
            'customer_name' => $addr?->contact_name ?: $user->name,
            'customer_phone' => $addr?->phone ?: $user->phone,
            'notes' => $instructions,
            'placed_at' => now(),
        ]);

        if (! empty($quote['promotion_id'])) {
            PromotionCalculator::markUsed($quote['promotion_id']);
        }

        StaffNotifier::notifyManagers(
            'New online order',
            "Order {$orderNumber} placed by {$user->name} — Rs. ".number_format($quote['total']).'.',
            'order',
            route('delivery.index', ['tab' => 'queue'])
        );

        $success = 'Order placed successfully! Order Number: '.$orderNumber;
        if ($quote['discount'] > 0) {
            $success .= ' (Promo saved you Rs. '.number_format($quote['discount']).')';
        } elseif ($quote['free_delivery'] && $quote['promo_code']) {
            $success .= ' (Free delivery applied)';
        }

        return redirect()->route('home')
            ->with('success', $success)
            ->with('clear_cart', true);
    }
}
