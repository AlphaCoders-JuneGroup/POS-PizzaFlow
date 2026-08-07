<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use App\Support\DeliveryDispatch;
use Illuminate\Database\Seeder;

class DeliveryDispatchSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@pizzaflow.com')->first();
        $driver = User::where('email', 'driver@pizzaflow.com')->first();
        $driverTwo = User::where('email', 'driver2@pizzaflow.com')->first();

        if (! $customer) {
            return;
        }

        if ($driver) {
            $prefs = $driver->preferences ?? [];
            $prefs['service_zone'] = 'Colombo';
            $driver->forceFill(['preferences' => $prefs])->save();
        }

        if ($driverTwo) {
            $prefs = $driverTwo->preferences ?? [];
            $prefs['service_zone'] = 'Nugegoda';
            $driverTwo->forceFill(['preferences' => $prefs])->save();
        }

        $samples = [
            [
                'order_number' => 'PF-D1001',
                'status' => 'preparing',
                'delivery_address' => '42 Flower Road',
                'delivery_city' => 'Colombo',
                'delivery_landmark' => 'Near the park',
                'delivery_instructions' => 'Ring the doorbell. Leave at the gate if no answer.',
                'items' => [
                    ['name' => 'Pepperoni Feast', 'qty' => 1, 'price' => 2290],
                    ['name' => 'Garlic Bread', 'qty' => 1, 'price' => 650],
                ],
                'subtotal' => 2940,
                'delivery_fee' => 250,
                'total' => 3190,
                'placed_at' => now()->subMinutes(35),
                'driver_id' => null,
            ],
            [
                'order_number' => 'PF-D1002',
                'status' => 'preparing',
                'delivery_address' => '18 High Level Road',
                'delivery_city' => 'Nugegoda',
                'delivery_landmark' => 'Opposite the bus stand',
                'delivery_instructions' => 'Call on arrival. Apartment 3B.',
                'items' => [
                    ['name' => 'Truffle Mushroom', 'qty' => 2, 'price' => 2690],
                ],
                'subtotal' => 5380,
                'delivery_fee' => 0,
                'total' => 5380,
                'placed_at' => now()->subMinutes(20),
                'driver_id' => null,
            ],
            [
                'order_number' => 'PF-D1003',
                'status' => 'preparing',
                'delivery_address' => '7 Marine Drive',
                'delivery_city' => 'Dehiwala',
                'delivery_landmark' => 'Blue gate house',
                'delivery_instructions' => 'Hand to security desk.',
                'items' => [
                    ['name' => 'Margherita Classic', 'qty' => 1, 'price' => 1890],
                ],
                'subtotal' => 1890,
                'delivery_fee' => 250,
                'total' => 2140,
                'placed_at' => now()->subMinutes(12),
                'driver_id' => null,
            ],
            [
                'order_number' => 'PF-D1004',
                'status' => 'out_for_delivery',
                'delivery_address' => '55 Bauddhaloka Mawatha',
                'delivery_city' => 'Colombo',
                'delivery_landmark' => 'Office lobby',
                'delivery_instructions' => 'Ask for Amaya at reception.',
                'items' => [
                    ['name' => 'Spicy Diavola', 'qty' => 1, 'price' => 2390],
                    ['name' => 'Coke 1.5L', 'qty' => 1, 'price' => 350],
                ],
                'subtotal' => 2740,
                'delivery_fee' => 250,
                'total' => 2990,
                'placed_at' => now()->subHours(1),
                'driver_id' => $driver?->_id,
                'assigned_at' => now()->subMinutes(25),
                'dispatched_at' => now()->subMinutes(15),
            ],
        ];

        foreach ($samples as $sample) {
            $city = $sample['delivery_city'];
            $route = DeliveryDispatch::estimateRoute($city, $sample['delivery_address']);

            $payload = [
                'user_id' => $customer->_id,
                'order_number' => $sample['order_number'],
                'items' => $sample['items'],
                'subtotal' => $sample['subtotal'],
                'delivery_fee' => $sample['delivery_fee'],
                'total' => $sample['total'],
                'status' => $sample['status'],
                'payment_method' => 'Cash on Delivery',
                'payment_status' => 'Pending',
                'delivery_address' => $sample['delivery_address'],
                'delivery_city' => $sample['delivery_city'],
                'delivery_landmark' => $sample['delivery_landmark'],
                'delivery_instructions' => $sample['delivery_instructions'],
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'notes' => $sample['delivery_instructions'],
                'driver_id' => $sample['driver_id'] ? (string) $sample['driver_id'] : null,
                'assigned_at' => $sample['assigned_at'] ?? null,
                'dispatched_at' => $sample['dispatched_at'] ?? null,
                'route_distance_km' => $sample['driver_id'] ? $route['distance_km'] : null,
                'route_eta_minutes' => $sample['driver_id'] ? $route['eta_minutes'] : null,
                'route_summary' => $sample['driver_id'] ? $route['summary'] : null,
                'placed_at' => $sample['placed_at'],
            ];

            Order::updateOrCreate(
                ['order_number' => $sample['order_number']],
                $payload
            );
        }

        // Enrich older sample orders with city/customer fields for dispatch UI.
        Order::where('order_number', 'PF-10045')->update([
            'delivery_city' => 'Colombo',
            'delivery_landmark' => 'Near the park',
            'delivery_instructions' => 'Ring the doorbell',
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'status' => 'preparing',
            'driver_id' => null,
        ]);
    }
}
