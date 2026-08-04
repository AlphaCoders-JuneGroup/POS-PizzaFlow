<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'orders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'items',
        'subtotal',
        'delivery_fee',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'delivery_address',
        'notes',
        'placed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'subtotal' => 'integer',
            'delivery_fee' => 'integer',
            'total' => 'integer',
            'placed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'preparing' => 'Preparing',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'delivered' => 'success',
            'preparing', 'out_for_delivery' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
