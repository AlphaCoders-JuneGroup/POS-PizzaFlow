<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'favorites';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'pizza_slug',
        'pizza_name',
        'pizza_image',
        'pizza_price',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pizza_price' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
