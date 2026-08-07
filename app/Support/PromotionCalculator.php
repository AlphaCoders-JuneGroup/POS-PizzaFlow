<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Collection;

class PromotionCalculator
{
    public const BASE_DELIVERY_FEE = 250;

    /**
     * @return array{
     *     subtotal: int,
     *     delivery_fee: int,
     *     discount: int,
     *     total: int,
     *     promo_code: ?string,
     *     promotion_id: ?string,
     *     promotion_title: ?string,
     *     message: ?string,
     *     free_delivery: bool
     * }
     */
    public static function quote(int $subtotal, ?string $promoCode = null, ?User $user = null): array
    {
        $subtotal = max(0, $subtotal);
        $active = self::activePromotions();

        $deliveryFee = self::BASE_DELIVERY_FEE;
        $autoFreeDelivery = self::autoFreeDeliveryThreshold($active);

        if ($autoFreeDelivery !== null && $subtotal >= $autoFreeDelivery) {
            $deliveryFee = 0;
        }

        $result = [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => 0,
            'total' => $subtotal + $deliveryFee,
            'promo_code' => null,
            'promotion_id' => null,
            'promotion_title' => null,
            'message' => null,
            'free_delivery' => $deliveryFee === 0,
        ];

        $code = strtoupper(trim((string) $promoCode));
        if ($code === '') {
            return $result;
        }

        // Look up by code even if expired, so we can return a clear message.
        $promotion = Promotion::where('promo_code', $code)->first();

        if (! $promotion) {
            $result['message'] = 'Invalid or inactive promo code.';

            return $result;
        }

        if (! $promotion->is_active) {
            $result['message'] = 'This promo code is inactive.';

            return $result;
        }

        if ($promotion->starts_at && now()->lt($promotion->starts_at)) {
            $result['message'] = 'This promo is not active yet.';

            return $result;
        }

        if ($promotion->ends_at && now()->gt($promotion->ends_at)) {
            $result['message'] = 'This promo code has expired.';

            return $result;
        }

        if (! $promotion->hasUsesRemaining()) {
            $result['message'] = 'This promo code has reached its usage limit.';

            return $result;
        }

        if ((int) $promotion->min_order_amount > 0 && $subtotal < (int) $promotion->min_order_amount) {
            $result['message'] = 'Add items worth at least Rs. '
                .number_format((int) $promotion->min_order_amount)
                .' to use this code.';

            return $result;
        }

        if ($promotion->first_order_only && $user && self::hasPriorOrders($user)) {
            $result['message'] = 'This code is only valid on your first order.';

            return $result;
        }

        $discount = 0;
        $type = $promotion->discount_type ?: Promotion::DISCOUNT_NONE;

        if ($type === Promotion::DISCOUNT_PERCENT) {
            $percent = max(0, min(100, (float) $promotion->discount_value));
            $discount = (int) round($subtotal * ($percent / 100));
            $cap = (int) ($promotion->max_discount ?? 0);
            if ($cap > 0) {
                $discount = min($discount, $cap);
            }
        } elseif ($type === Promotion::DISCOUNT_FIXED) {
            $discount = (int) min($subtotal, max(0, (int) $promotion->discount_value));
        } elseif ($type === Promotion::DISCOUNT_FREE_DELIVERY) {
            $deliveryFee = 0;
        }

        $total = max(0, $subtotal - $discount) + $deliveryFee;

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'total' => $total,
            'promo_code' => $code,
            'promotion_id' => (string) $promotion->_id,
            'promotion_title' => $promotion->title,
            'message' => 'Promo applied: '.$promotion->title,
            'free_delivery' => $deliveryFee === 0,
        ];
    }

    /**
     * @return Collection<int, Promotion>
     */
    public static function activePromotions(): Collection
    {
        return Promotion::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Promotion $promo) => $promo->isCurrentlyValid())
            ->values();
    }

    /**
     * Lowest min-order for auto free-delivery promos (no code required).
     *
     * @param  Collection<int, Promotion>  $promotions
     */
    public static function autoFreeDeliveryThreshold(Collection $promotions): ?int
    {
        $thresholds = $promotions
            ->filter(function (Promotion $promo) {
                return $promo->discount_type === Promotion::DISCOUNT_FREE_DELIVERY
                    && empty($promo->promo_code);
            })
            ->map(fn (Promotion $promo) => (int) ($promo->min_order_amount ?: 0))
            ->filter(fn (int $amount) => $amount > 0)
            ->values();

        return $thresholds->isEmpty() ? null : $thresholds->min();
    }

    public static function markUsed(?string $promotionId): void
    {
        if (! $promotionId) {
            return;
        }

        $promo = Promotion::find($promotionId);
        if (! $promo) {
            return;
        }

        $promo->used_count = (int) ($promo->used_count ?? 0) + 1;
        $promo->save();
    }

    private static function hasPriorOrders(User $user): bool
    {
        return Order::where('user_id', (string) $user->_id)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }
}
