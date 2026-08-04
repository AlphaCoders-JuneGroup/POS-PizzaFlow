<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case StoreManager = 'store_manager';
    case Customer = 'customer';
    case KitchenStaff = 'kitchen_staff';
    case DeliveryDriver = 'delivery_driver';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::StoreManager => 'Store Manager',
            self::Customer => 'Customer',
            self::KitchenStaff => 'Kitchen Staff',
            self::DeliveryDriver => 'Delivery Driver',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'dashboard.admin',
            self::StoreManager => 'dashboard.manager',
            self::Customer => 'home',
            self::KitchenStaff => 'dashboard.kitchen',
            self::DeliveryDriver => 'dashboard.driver',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [
            self::Admin,
            self::StoreManager,
            self::KitchenStaff,
            self::DeliveryDriver,
        ], true);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Roles that can self-register publicly. */
    public static function publicRegistrationRoles(): array
    {
        return [self::Customer];
    }
}
