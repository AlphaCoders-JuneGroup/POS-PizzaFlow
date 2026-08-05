<?php

namespace App\Models;

use App\Enums\IngredientCategory;
use App\Enums\IngredientUsageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'ingredients';

    protected $fillable = [
        'name',
        'category',
        'unit',
        'stock_quantity',
        'reorder_level',
        'is_out_of_stock',
        'notes',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => IngredientCategory::class,
            'stock_quantity' => 'float',
            'reorder_level' => 'float',
            'is_out_of_stock' => 'boolean',
        ];
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(IngredientUsageLog::class);
    }

    /**
     * Low stock = still available but at/under the reorder threshold.
     */
    public function isLowStock(): bool
    {
        return ! $this->is_out_of_stock && $this->stock_quantity <= $this->reorder_level;
    }

    /**
     * Deduct stock when an order consumes this ingredient and dynamically
     * flag it out of stock if depleted. Intended to be called from the
     * Order Processing module (KDS/order flow) whenever an order is placed
     * or prepared, e.g.:
     *
     *   Ingredient::deductStock($toppingIngredientId, 0.05, $order->_id);
     */
    public static function deductStock(string $ingredientId, float $quantity, ?string $orderId = null): self
    {
        $ingredient = self::findOrFail($ingredientId);

        $ingredient->stock_quantity = max(0, $ingredient->stock_quantity - $quantity);
        $ingredient->is_out_of_stock = $ingredient->stock_quantity <= 0;
        $ingredient->save();

        IngredientUsageLog::create([
            'ingredient_id' => $ingredient->_id,
            'type' => IngredientUsageType::OrderDeduction->value,
            'quantity' => $quantity,
            'order_id' => $orderId,
            'usage_date' => now()->toDateString(),
        ]);

        return $ingredient;
    }
}
