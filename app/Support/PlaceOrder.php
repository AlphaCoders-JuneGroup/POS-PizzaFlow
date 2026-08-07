<?php

namespace App\Support;

use App\Models\Order;
use App\Models\User;

class PlaceOrder
{
    /**
     * @param  list<array<string, mixed>>  $cartItems
     * @param  array{
     *     name: string,
     *     phone?: ?string,
     *     address?: string,
     *     city?: ?string,
     *     landmark?: ?string,
     *     instructions?: ?string,
     *     fulfillment_type?: string
     * }  $customer
     * @return array{order: Order, promo_warning: ?string, message: string}
     */
    public static function create(array $cartItems, array $customer, ?User $user = null, ?string $promoCode = null): array
    {
        $subtotal = 0;
        $formattedItems = [];

        foreach ($cartItems as $item) {
            $itemPrice = (int) ($item['price'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $subtotal += $itemPrice * $qty;

            $baseName = (string) ($item['name'] ?? 'Item');
            $size = ! empty($item['size']) ? (string) $item['size'] : null;
            $crust = ! empty($item['crust']) ? (string) $item['crust'] : null;
            $sauce = ! empty($item['sauce']) ? (string) $item['sauce'] : null;
            $toppingNames = [];
            if (! empty($item['toppings']) && is_array($item['toppings'])) {
                $toppingNames = array_values(array_filter(array_map(
                    fn ($t) => is_array($t) ? (string) ($t['name'] ?? '') : (string) $t,
                    $item['toppings']
                )));
            }

            $name = $baseName;
            if ($size || $crust || $sauce || $toppingNames !== []) {
                $customizations = array_values(array_filter([
                    $size,
                    $crust,
                    $toppingNames !== [] ? implode(', ', $toppingNames) : null,
                    $sauce,
                ]));
                $name .= ' ['.implode(' | ', $customizations).']';
            }

            $formattedItems[] = [
                'name' => $name,
                'base_name' => $baseName,
                'qty' => $qty,
                'price' => $itemPrice,
                'size' => $size,
                'crust' => $crust,
                'sauce' => $sauce,
                'toppings' => $toppingNames,
                'kds_status' => Order::ITEM_KDS_PENDING,
            ];
        }

        $requestedCode = trim((string) ($promoCode ?? ''));
        $quote = PromotionCalculator::quote($subtotal, $requestedCode !== '' ? $requestedCode : null, $user);

        $promoWarning = null;
        if ($requestedCode !== '' && empty($quote['promo_code'])) {
            $promoWarning = $quote['message'] ?? 'Promo code could not be applied.';
            $quote = PromotionCalculator::quote($subtotal, null, $user);
        }

        $fulfillment = in_array($customer['fulfillment_type'] ?? '', ['pickup', 'delivery'], true)
            ? $customer['fulfillment_type']
            : 'delivery';

        $address = trim((string) ($customer['address'] ?? ''));
        if ($address === '' || strcasecmp($address, 'Pickup') === 0) {
            $fulfillment = 'pickup';
            $address = 'Pickup';
        }

        $orderNumber = 'PF-'.mt_rand(10000, 99999);
        $isPickup = $fulfillment === 'pickup';

        $order = Order::create([
            'user_id' => $user?->_id,
            'order_number' => $orderNumber,
            'items' => $formattedItems,
            'subtotal' => $quote['subtotal'],
            'delivery_fee' => $isPickup ? 0 : $quote['delivery_fee'],
            'discount' => $quote['discount'],
            'total' => $isPickup
                ? max(0, $quote['subtotal'] - $quote['discount'])
                : $quote['total'],
            'status' => Order::STATUS_RECEIVED,
            'fulfillment_type' => $fulfillment,
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Pending',
            'promo_code' => $quote['promo_code'],
            'promotion_id' => $quote['promotion_id'],
            'promotion_title' => $quote['promotion_title'],
            'delivery_address' => $address,
            'delivery_city' => $customer['city'] ?? null,
            'delivery_landmark' => $customer['landmark'] ?? null,
            'delivery_instructions' => $customer['instructions'] ?? null,
            'customer_name' => $customer['name'],
            'customer_phone' => $customer['phone'] ?? null,
            'notes' => $customer['instructions'] ?? null,
            'placed_at' => now(),
            'status_updated_at' => now(),
        ]);

        if (! empty($quote['promotion_id'])) {
            PromotionCalculator::markUsed($quote['promotion_id']);
        }

        StaffNotifier::notifyManagers(
            'New online order',
            "Order {$orderNumber} placed by {$customer['name']} — Rs. ".number_format((int) $order->total).'.',
            'order',
            route('orders.manage.index', ['status' => 'received', 'type' => '', 'q' => $orderNumber])
        );

        StaffNotifier::notifyKitchen(
            'New ticket on KDS',
            "Order {$orderNumber} — {$customer['name']}. Check the kitchen board.",
            'order',
            route('kds.index')
        );

        $message = 'Order placed successfully! Order Number: '.$orderNumber;
        if ($quote['discount'] > 0) {
            $message .= ' (Promo saved you Rs. '.number_format($quote['discount']).')';
        } elseif (! empty($quote['free_delivery']) && $quote['promo_code']) {
            $message .= ' (Free delivery applied)';
        }

        return [
            'order' => $order,
            'promo_warning' => $promoWarning,
            'message' => $message,
        ];
    }
}
