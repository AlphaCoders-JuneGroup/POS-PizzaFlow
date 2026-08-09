<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Buy 1 Get 1 Free',
                'description' => 'Order any large pizza and get a medium of equal or lesser value free.',
                'type' => Promotion::TYPE_CARD,
                'icon' => '🔥',
                'button_text' => 'Claim Offer',
                'button_link' => '#menu',
                'promo_code' => 'BOGO',
                'discount_type' => Promotion::DISCOUNT_PERCENT,
                'discount_value' => 50,
                'max_discount' => 1500,
                'min_order_amount' => 0,
                'usage_limit' => 200,
                'used_count' => 0,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addDays(30)->endOfDay(),
                'first_order_only' => false,
                'style' => 'style_1',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Weekend Combo',
                'description' => '2 Large pizzas + 2 drinks + garlic bread for only Rs. 4,990.',
                'type' => Promotion::TYPE_CARD,
                'icon' => '🔥',
                'button_text' => 'Claim Offer',
                'button_link' => '#menu',
                'promo_code' => 'WEEKEND',
                'discount_type' => Promotion::DISCOUNT_FIXED,
                'discount_value' => 500,
                'max_discount' => 0,
                'min_order_amount' => 4000,
                'usage_limit' => 100,
                'used_count' => 0,
                'starts_at' => now()->startOfWeek(),
                'ends_at' => now()->endOfWeek(),
                'first_order_only' => false,
                'style' => 'style_2',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Free Delivery Over Rs.5000',
                'description' => 'Enjoy complimentary delivery on all orders above Rs. 5,000.',
                'type' => Promotion::TYPE_CARD,
                'icon' => '🔥',
                'button_text' => 'Order Now',
                'button_link' => '#menu',
                'promo_code' => null,
                'discount_type' => Promotion::DISCOUNT_FREE_DELIVERY,
                'discount_value' => 0,
                'max_discount' => 0,
                'min_order_amount' => 5000,
                'usage_limit' => 0,
                'used_count' => 0,
                'starts_at' => now()->subDays(7)->startOfDay(),
                'ends_at' => now()->addMonths(3)->endOfDay(),
                'first_order_only' => false,
                'style' => 'style_3',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Hungry? Your next pizza is just a tap away.',
                'description' => 'Use code FLOW20 for 20% off your first order.',
                'type' => Promotion::TYPE_BANNER,
                'icon' => null,
                'button_text' => 'Get Started',
                'button_link' => '#menu',
                'promo_code' => 'FLOW20',
                'discount_type' => Promotion::DISCOUNT_PERCENT,
                'discount_value' => 20,
                'max_discount' => 1000,
                'min_order_amount' => 0,
                'usage_limit' => 500,
                'used_count' => 0,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addDays(60)->endOfDay(),
                'first_order_only' => true,
                'style' => 'style_2',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Promotion::updateOrCreate(
                ['title' => $item['title'], 'type' => $item['type']],
                $item
            );
        }
    }
}
