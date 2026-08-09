<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PizzaTopping extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'pizza_toppings';

    protected $fillable = [
        'name',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
