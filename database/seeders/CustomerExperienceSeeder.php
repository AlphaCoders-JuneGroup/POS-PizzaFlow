<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\User;
use App\Support\PizzaCatalog;
use Illuminate\Database\Seeder;

class CustomerExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@pizzaflow.com')->first();

        if (! $customer) {
            return;
        }

        // Sample favorites
        if ($customer->favorites()->count() === 0) {
            foreach (['margherita-classic', 'pepperoni-feast', 'truffle-mushroom'] as $slug) {
                $pizza = PizzaCatalog::find($slug);
                if (! $pizza) {
                    continue;
                }

                Favorite::create([
                    'user_id' => $customer->_id,
                    'pizza_slug' => $pizza['slug'],
                    'pizza_name' => $pizza['name'],
                    'pizza_image' => $pizza['image'],
                    'pizza_price' => $pizza['price'],
                ]);
            }
        }

        // Sample order history
        if ($customer->orders()->count() === 0) {
            $samples = [
                [
                    'order_number' => 'PF-10021',
                    'status' => 'delivered',
                    'payment_method' => 'Card',
                    'payment_status' => 'Paid',
                    'items' => [
                        ['name' => 'Margherita Classic', 'qty' => 1, 'price' => 1890],
                        ['name' => 'Garlic Bread', 'qty' => 1, 'price' => 650],
                    ],
                    'subtotal' => 2540,
                    'delivery_fee' => 250,
                    'total' => 2790,
                    'placed_at' => now()->subDays(6),
                    'delivery_address' => '42 Flower Road, Colombo',
                ],
                [
                    'order_number' => 'PF-10045',
                    'status' => 'out_for_delivery',
                    'payment_method' => 'Cash on Delivery',
                    'payment_status' => 'Pending',
                    'items' => [
                        ['name' => 'Pepperoni Feast', 'qty' => 2, 'price' => 2290],
                    ],
                    'subtotal' => 4580,
                    'delivery_fee' => 0,
                    'total' => 4580,
                    'placed_at' => now()->subHours(2),
                    'delivery_address' => '42 Flower Road, Colombo',
                ],
                [
                    'order_number' => 'PF-10012',
                    'status' => 'cancelled',
                    'payment_method' => 'Card',
                    'payment_status' => 'Refunded',
                    'items' => [
                        ['name' => 'Spicy Diavola', 'qty' => 1, 'price' => 2390],
                    ],
                    'subtotal' => 2390,
                    'delivery_fee' => 250,
                    'total' => 2640,
                    'placed_at' => now()->subDays(12),
                    'delivery_address' => '42 Flower Road, Colombo',
                ],
            ];

            foreach ($samples as $sample) {
                Order::create([
                    'user_id' => $customer->_id,
                    ...$sample,
                    'notes' => 'Ring the doorbell',
                ]);
            }
        }

        if (! $customer->last_login_at) {
            $customer->forceFill([
                'last_login_at' => now()->subDay(),
                'last_login_ip' => '127.0.0.1',
                'login_count' => max(1, (int) $customer->login_count),
            ])->save();
        }
    }
}
