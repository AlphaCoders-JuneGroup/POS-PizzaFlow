<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Seed demo ingredients/toppings for the Inventory & Item Control module.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Mozzarella Cheese', 'category' => 'cheese', 'unit' => 'kg', 'stock_quantity' => 12, 'reorder_level' => 3],
            ['name' => 'Cheddar Cheese', 'category' => 'cheese', 'unit' => 'kg', 'stock_quantity' => 5, 'reorder_level' => 2],
            ['name' => 'Pepperoni', 'category' => 'topping', 'unit' => 'kg', 'stock_quantity' => 8, 'reorder_level' => 2],
            ['name' => 'Mushroom', 'category' => 'topping', 'unit' => 'kg', 'stock_quantity' => 1.5, 'reorder_level' => 2],
            ['name' => 'Black Olives', 'category' => 'topping', 'unit' => 'kg', 'stock_quantity' => 0, 'reorder_level' => 1],
            ['name' => 'Bell Peppers', 'category' => 'topping', 'unit' => 'kg', 'stock_quantity' => 4, 'reorder_level' => 1.5],
            ['name' => 'Classic Pizza Dough', 'category' => 'base', 'unit' => 'pcs', 'stock_quantity' => 60, 'reorder_level' => 20],
            ['name' => 'Thin Crust Base', 'category' => 'crust', 'unit' => 'pcs', 'stock_quantity' => 25, 'reorder_level' => 10],
            ['name' => 'Tomato Sauce', 'category' => 'sauce', 'unit' => 'l', 'stock_quantity' => 10, 'reorder_level' => 3],
            ['name' => 'BBQ Sauce', 'category' => 'sauce', 'unit' => 'l', 'stock_quantity' => 2, 'reorder_level' => 2],
            ['name' => 'Pizza Boxes (Medium)', 'category' => 'packaging', 'unit' => 'pcs', 'stock_quantity' => 150, 'reorder_level' => 50],
        ];

        foreach ($items as $data) {
            Ingredient::updateOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['is_out_of_stock' => $data['stock_quantity'] <= 0])
            );
        }
    }
}
