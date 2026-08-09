<?php

namespace App\Enums;

enum IngredientCategory: string
{
    case Topping = 'topping';
    case Base = 'base';
    case Sauce = 'sauce';
    case Cheese = 'cheese';
    case Crust = 'crust';
    case Packaging = 'packaging';

    public function label(): string
    {
        return match ($this) {
            self::Topping => 'Topping',
            self::Base => 'Base / Dough',
            self::Sauce => 'Sauce',
            self::Cheese => 'Cheese',
            self::Crust => 'Crust',
            self::Packaging => 'Packaging',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Topping => 'bi-egg-fried',
            self::Base => 'bi-circle',
            self::Sauce => 'bi-droplet',
            self::Cheese => 'bi-triangle',
            self::Crust => 'bi-border-style',
            self::Packaging => 'bi-box-seam',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
