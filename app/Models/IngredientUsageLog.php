<?php

namespace App\Models;

use App\Enums\IngredientUsageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class IngredientUsageLog extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'ingredient_usage_logs';

    protected $fillable = [
        'ingredient_id',
        'type',
        'quantity',
        'order_id',
        'usage_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'type' => IngredientUsageType::class,
            'quantity' => 'float',
            'usage_date' => 'date',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
