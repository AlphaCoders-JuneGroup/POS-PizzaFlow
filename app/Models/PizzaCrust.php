<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PizzaCrust extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'pizza_crusts';

    protected $fillable = [
        'name',
        'price_modifier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_modifier' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
