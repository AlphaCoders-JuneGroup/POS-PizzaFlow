<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\StaffNotifier;
use Illuminate\Database\Seeder;

class StaffNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::whereIn('role', [
            UserRole::Admin->value,
            UserRole::StoreManager->value,
            UserRole::DeliveryDriver->value,
            UserRole::KitchenStaff->value,
        ])->get();

        foreach ($staff as $user) {
            $existing = \App\Models\StaffNotification::where('user_id', (string) $user->_id)->count();
            if ($existing > 0) {
                continue;
            }

            $samples = match ($user->role instanceof UserRole ? $user->role : UserRole::tryFrom((string) $user->role)) {
                UserRole::DeliveryDriver => [
                    [
                        'title' => 'New delivery assigned',
                        'body' => 'Order PF-D1004 is ready for pickup at the kitchen.',
                        'type' => 'delivery',
                        'link' => route('delivery.index'),
                    ],
                    [
                        'title' => 'Route tip',
                        'body' => 'Check delivery instructions before you start the run.',
                        'type' => 'delivery',
                        'link' => route('delivery.index'),
                    ],
                ],
                UserRole::KitchenStaff => [
                    [
                        'title' => 'Kitchen queue update',
                        'body' => 'New orders are waiting on the KDS board.',
                        'type' => 'order',
                        'link' => route('dashboard.kitchen'),
                    ],
                ],
                default => [
                    [
                        'title' => 'New online order',
                        'body' => 'A customer just placed an order. Check Delivery & Dispatch.',
                        'type' => 'order',
                        'link' => route('delivery.index'),
                    ],
                    [
                        'title' => 'Promotion live',
                        'body' => 'FLOW20 is active for first-time customers.',
                        'type' => 'promotion',
                        'link' => route('promotions.index'),
                    ],
                    [
                        'title' => 'Low stock reminder',
                        'body' => 'Review mozzarella stock in Inventory.',
                        'type' => 'inventory',
                        'link' => route('admin.inventory.index'),
                    ],
                ],
            };

            foreach ($samples as $sample) {
                StaffNotifier::send(
                    $user,
                    $sample['title'],
                    $sample['body'],
                    $sample['type'],
                    $sample['link'],
                );
            }
        }
    }
}
