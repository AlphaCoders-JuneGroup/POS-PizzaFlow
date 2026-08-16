<?php

namespace App\Http\Controllers;

use App\Support\PlaceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestCheckoutController extends Controller
{
    public function create(): View
    {
        return view('auth.guest-checkout', [
            'guest' => session('guest', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $fulfillment = $request->input('fulfillment_type', 'delivery');
        $needsAddress = $fulfillment !== 'pickup';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line' => [$needsAddress ? 'required' : 'nullable', 'string', 'max:255'],
            'city' => [$needsAddress ? 'required' : 'nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
            'fulfillment_type' => ['nullable', 'in:pickup,delivery'],
            'cart_data' => ['nullable', 'string'],
            'promo_code' => ['nullable', 'string', 'max:40'],
        ]);

        $guest = collect($validated)->except(['cart_data', 'promo_code', 'fulfillment_type'])->all();
        $request->session()->put('guest_checkout', true);
        $request->session()->put('guest', $guest);

        $cartItems = OrderController::decodeCart($validated['cart_data'] ?? null);
        if ($cartItems === []) {
            return redirect()
                ->route('home')
                ->with('success', 'Guest details saved. Add pizzas to your cart, then open the cart and tap Place Order.');
        }

        $fulfillment = $validated['fulfillment_type'] ?? 'delivery';
        $address = $fulfillment === 'pickup'
            ? 'Pickup'
            : trim(($guest['address_line'] ?? '').', '.($guest['city'] ?? ''), ' ,');

        $result = PlaceOrder::create(
            $cartItems,
            [
                'name' => (string) $guest['name'],
                'phone' => (string) ($guest['phone'] ?? ''),
                'address' => $address,
                'city' => $fulfillment === 'pickup' ? null : ($guest['city'] ?? null),
                'landmark' => $guest['postal_code'] ?? null,
                'instructions' => $guest['delivery_notes'] ?? null,
                'fulfillment_type' => $fulfillment,
                'payment_method' => $request->input('payment_method', 'Cash on Delivery'),
            ],
            null,
            $validated['promo_code'] ?? null
        );

        if (! empty($result['stripe_url'])) {
            return redirect()->away($result['stripe_url']);
        }

        $redirect = redirect()->route('home')
            ->with('success', $result['message'].' No login needed — staff can see it in Order Management.')
            ->with('clear_cart', true);

        if ($result['promo_warning']) {
            $redirect->with('error', $result['promo_warning'].' Order was placed without that discount.');
        }

        return $redirect;
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        if (! $request->session()->get('guest_checkout') || ! is_array($request->session()->get('guest'))) {
            return redirect()
                ->route('guest.create')
                ->with('error', 'Enter your details to order without an account.');
        }

        $cartItems = OrderController::decodeCart($request->input('cart_data'));
        if ($cartItems === []) {
            return redirect()->route('home')->with('error', 'Your cart is empty.');
        }

        $guest = $request->session()->get('guest');
        $fulfillment = in_array($request->input('fulfillment_type'), ['pickup', 'delivery'], true)
            ? $request->input('fulfillment_type')
            : 'delivery';

        $result = PlaceOrder::create(
            $cartItems,
            [
                'name' => (string) $guest['name'],
                'phone' => (string) ($guest['phone'] ?? ''),
                'address' => $fulfillment === 'pickup'
                    ? 'Pickup'
                    : trim(($guest['address_line'] ?? '').', '.($guest['city'] ?? ''), ' ,'),
                'city' => $fulfillment === 'pickup' ? null : ($guest['city'] ?? null),
                'landmark' => $guest['postal_code'] ?? null,
                'instructions' => $guest['delivery_notes'] ?? null,
                'fulfillment_type' => $fulfillment,
                'payment_method' => $request->input('payment_method', 'Cash on Delivery'),
            ],
            null,
            $request->input('promo_code')
        );

        if (! empty($result['stripe_url'])) {
            return redirect()->away($result['stripe_url']);
        }

        $redirect = redirect()->route('home')
            ->with('success', $result['message'].' Staff can see it in Order Management.')
            ->with('clear_cart', true);

        if ($result['promo_warning']) {
            $redirect->with('error', $result['promo_warning'].' Order was placed without that discount.');
        }

        return $redirect;
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(['guest_checkout', 'guest']);

        return back()->with('success', 'Guest checkout cleared.');
    }
}
