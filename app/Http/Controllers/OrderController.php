<?php

namespace App\Http\Controllers;

use App\Support\PlaceOrder;
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

        $cartItems = self::decodeCart($request->input('cart_data'));
        if ($cartItems === []) {
            return back()->with('error', 'Your cart is empty.');
        }

        $addr = $user->defaultAddress();
        $isPickup = ! $addr;
        $instructions = $user->preferences['delivery_notes'] ?? '';

        $result = PlaceOrder::create(
            $cartItems,
            [
                'name' => $addr?->contact_name ?: $user->name,
                'phone' => $addr?->phone ?: $user->phone,
                'address' => $addr ? $addr->address_line.', '.$addr->city : 'Pickup',
                'city' => $addr?->city,
                'landmark' => $addr?->landmark,
                'instructions' => $instructions,
                'fulfillment_type' => $isPickup ? 'pickup' : 'delivery',
                'payment_method' => $request->input('payment_method', 'Cash on Delivery'),
            ],
            $user,
            $request->input('promo_code')
        );

        if (! empty($result['stripe_url'])) {
            return redirect()->away($result['stripe_url']);
        }

        $redirect = redirect()->route('home')
            ->with('success', $result['message'])
            ->with('clear_cart', true);

        if ($result['promo_warning']) {
            $redirect->with('error', $result['promo_warning'].' Order was placed without that discount.');
        }

        return $redirect;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function decodeCart(mixed $cartDataRaw): array
    {
        if (! $cartDataRaw) {
            return [];
        }

        $decoded = is_string($cartDataRaw) ? json_decode($cartDataRaw, true) : $cartDataRaw;

        return is_array($decoded) ? array_values($decoded) : [];
    }
}
