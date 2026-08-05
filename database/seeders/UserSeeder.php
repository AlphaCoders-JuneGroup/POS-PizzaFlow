<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed demo users for every PizzaFlow role.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Admin',
                'email' => 'admin@pizzaflow.com',
                'phone' => '+94112233440',
                'role' => UserRole::Admin,
                'password' => 'Password123!',
            ],
            [
                'name' => 'Nimal Store Manager',
                'email' => 'manager@pizzaflow.com',
                'phone' => '+94112233441',
                'role' => UserRole::StoreManager,
                'password' => 'Password123!',
            ],
            [
                'name' => 'Amaya Customer',
                'email' => 'customer@pizzaflow.com',
                'phone' => '+94771234567',
                'role' => UserRole::Customer,
                'password' => 'Password123!',
            ],
            [
                'name' => 'Kasun Kitchen',
                'email' => 'kitchen@pizzaflow.com',
                'phone' => '+94112233442',
                'role' => UserRole::KitchenStaff,
                'password' => 'Password123!',
            ],
            [
                'name' => 'Dilshan Driver',
                'email' => 'driver@pizzaflow.com',
                'phone' => '+94779876543',
                'role' => UserRole::DeliveryDriver,
                'password' => 'Password123!',
            ],
        ];

        foreach ($users as $data) {
            $password = $data['password'];
            unset($data['password']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    ...$data,
                    'password' => $password,
                    'is_active' => true,
                    'preferences' => [
                        'preferred_crust' => 'classic',
                        'spice_level' => 'medium',
                        'allergies' => '',
                        'delivery_notes' => 'Ring the doorbell',
                    ],
                ]
            );

            if ($user->role === UserRole::Customer && $user->addresses()->count() === 0) {
                Address::create([
                    'user_id' => $user->_id,
                    'label' => 'Home',
                    'contact_name' => $user->name,
                    'phone' => $user->phone,
                    'address_line' => '42 Flower Road',
                    'city' => 'Colombo',
                    'postal_code' => '00700',
                    'landmark' => 'Near the park',
                    'is_default' => true,
                ]);
            }
        }
    }
}
