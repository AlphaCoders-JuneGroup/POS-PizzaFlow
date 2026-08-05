<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $cartDataRaw = $request->input('cart_data');
        $cartItems = json_encode([]);
        if ($cartDataRaw) {
            $cartItems = json_decode($cartDataRaw, true);
        }

        if (empty($cartItems)) {
            return back()->with('error', 'Your cart is empty.');
        }

        // Calculate pricing
        $subtotal = 0;
        $formattedItems = [];

        foreach ($cartItems as $item) {
            $itemPrice = (int)$item['price'];
            $qty = (int)$item['qty'];
            $subtotal += $itemPrice * $qty;

            // Generate a descriptive item name for customized items
            $name = $item['name'];
            if (!empty($item['size']) || !empty($item['crust'])) {
                $customizations = [];
                if (!empty($item['size'])) {
                    $customizations[] = $item['size'];
                }
                if (!empty($item['crust'])) {
                    $customizations[] = $item['crust'];
                }
                if (!empty($item['toppings'])) {
                    $toppingNames = array_map(fn($t) => $t['name'], $item['toppings']);
                    $customizations[] = implode(', ', $toppingNames);
                }
                $name .= ' [' . implode(' | ', $customizations) . ']';
            }

            $formattedItems[] = [
                'name' => $name,
                'qty' => $qty,
                'price' => $itemPrice,
            ];
        }

        $deliveryFee = $subtotal > 4000 ? 0 : 250;
        $total = $subtotal + $deliveryFee;

        // Generate a random order number
        $orderNumber = 'PF-' . mt_rand(10000, 99999);

        // Get address
        $address = 'Pickup';
        if ($user->defaultAddress()) {
            $addr = $user->defaultAddress();
            $address = $addr->address_line . ', ' . $addr->city;
        }

        Order::create([
            'user_id' => $user->_id,
            'order_number' => $orderNumber,
            'items' => $formattedItems,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'status' => 'pending',
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Pending',
            'delivery_address' => $address,
            'notes' => $user->preferences['delivery_notes'] ?? '',
            'placed_at' => now(),
        ]);

        // We redirect to home and flash a flag to clear cart
        return redirect()->route('home')
            ->with('success', 'Order placed successfully! Order Number: ' . $orderNumber)
            ->with('clear_cart', true);
    }
}
