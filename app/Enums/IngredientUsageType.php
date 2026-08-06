<?php

namespace App\Enums;

enum IngredientUsageType: string
{
    case OrderDeduction = 'order_deduction';
    case Restock = 'restock';
    case Adjustment = 'adjustment';
    case Wastage = 'wastage';

    public function label(): string
    {
        return match ($this) {
            self::OrderDeduction => 'Used in Order',
            self::Restock => 'Restocked',
            self::Adjustment => 'Manual Adjustment',
            self::Wastage => 'Wastage / Spoilage',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::OrderDeduction => 'text-bg-danger',
            self::Restock => 'text-bg-success',
            self::Adjustment => 'text-bg-secondary',
            self::Wastage => 'text-bg-warning',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
