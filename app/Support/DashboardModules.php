<?php

namespace App\Support;

use App\Enums\UserRole;

class DashboardModules
{
    /**
     * @return list<array{key: string, title: string, short: string, description: string, icon: string, group: string, route: ?string, roles: list<string>}>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'users',
                'title' => 'User & Profile Management',
                'short' => 'Users & Profiles',
                'description' => 'Manage staff accounts, customer profiles, roles, and permissions.',
                'icon' => 'bi-people',
                'group' => 'People',
                'route' => 'users.index',
                'roles' => [UserRole::Admin->value, UserRole::StoreManager->value],
            ],
            [
                'key' => 'menu',
                'title' => 'Menu & Customization Engine',
                'short' => 'Menu Engine',
                'description' => 'Build pizza menus, sizes, crusts, toppings, and dynamic pricing.',
                'icon' => 'bi-menu-button-wide',
                'group' => 'Catalog',
                'route' => 'admin.menu.index',
                'roles' => [UserRole::Admin->value, UserRole::StoreManager->value],
            ],
            [
                'key' => 'promotions',
                'title' => 'Promotions & Offers',
                'short' => 'Promotions',
                'description' => 'Create special offers and promo banners shown on the homepage.',
                'icon' => 'bi-tag',
                'group' => 'Catalog',
                'route' => 'promotions.index',
                'roles' => [UserRole::Admin->value, UserRole::StoreManager->value],
            ],
            [
                'key' => 'orders',
                'title' => 'Order Management',
                'short' => 'Orders',
                'description' => 'Create, track, update, and cancel pickup or delivery orders.',
                'icon' => 'bi-receipt',
                'group' => 'Operations',
                'route' => null,
                'roles' => [
                    UserRole::Admin->value,
                    UserRole::StoreManager->value,
                    UserRole::KitchenStaff->value,
                    UserRole::DeliveryDriver->value,
                ],
            ],
            [
                'key' => 'billing',
                'title' => 'Billing & Payment',
                'short' => 'Billing',
                'description' => 'Handle payments, receipts, refunds, and payment status tracking.',
                'icon' => 'bi-credit-card',
                'group' => 'Operations',
                'route' => null,
                'roles' => [UserRole::Admin->value, UserRole::StoreManager->value],
            ],
            [
                'key' => 'kds',
                'title' => 'Kitchen Display System (KDS)',
                'short' => 'Kitchen KDS',
                'description' => 'Live kitchen queue with preparation status and completion tracking.',
                'icon' => 'bi-display',
                'group' => 'Operations',
                'route' => null,
                'roles' => [
                    UserRole::Admin->value,
                    UserRole::StoreManager->value,
                    UserRole::KitchenStaff->value,
                ],
            ],
            [
                'key' => 'delivery',
                'title' => 'Delivery & Dispatch',
                'short' => 'Delivery',
                'description' => 'Assign drivers by location, share routes, and track deliveries.',
                'icon' => 'bi-truck',
                'group' => 'Operations',
                'route' => 'delivery.index',
                'roles' => [
                    UserRole::Admin->value,
                    UserRole::StoreManager->value,
                    UserRole::DeliveryDriver->value,
                ],
            ],
            [
                'key' => 'inventory',
                'title' => 'Inventory & Stock Control',
                'short' => 'Inventory',
                'description' => 'Track ingredient stock, low-stock alerts, and topping availability.',
                'icon' => 'bi-box-seam',
                'group' => 'Supply',
                'route' => 'admin.inventory.index',
                'roles' => [UserRole::Admin->value, UserRole::StoreManager->value],
            ],
            [
                'key' => 'reports',
                'title' => 'Reports',
                'short' => 'Reports',
                'description' => 'Sales reports, popular pizzas, peak hours, and delivery performance.',
                'icon' => 'bi-graph-up-arrow',
                'group' => 'Insights',
                'route' => null,
                'roles' => [UserRole::Admin->value, UserRole::StoreManager->value],
            ],
        ];
    }

    /**
     * @return list<array{key: string, title: string, short: string, description: string, icon: string, group: string, route: ?string, roles: list<string>}>
     */
    public static function forRole(UserRole|string $role): array
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        if ($roleValue === UserRole::Admin->value) {
            return self::all();
        }

        return array_values(array_filter(
            self::all(),
            fn (array $module) => in_array($roleValue, $module['roles'], true)
        ));
    }

    /**
     * @return array<string, list<array{key: string, title: string, short: string, description: string, icon: string, group: string, route: ?string, roles: list<string>}>>
     */
    public static function groupedForRole(UserRole|string $role): array
    {
        $grouped = [];

        foreach (self::forRole($role) as $module) {
            $grouped[$module['group']][] = $module;
        }

        return $grouped;
    }
}
