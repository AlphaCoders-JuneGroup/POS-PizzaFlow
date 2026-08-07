<?php

namespace App\Models;

use App\Support\DeliveryDispatch;
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
        'discount',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'promo_code',
        'promotion_id',
        'promotion_title',
        'delivery_address',
        'delivery_city',
        'delivery_landmark',
        'delivery_instructions',
        'customer_name',
        'customer_phone',
        'notes',
        'driver_id',
        'assigned_at',
        'dispatched_at',
        'delivered_at',
        'route_distance_km',
        'route_eta_minutes',
        'route_summary',
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
            'discount' => 'integer',
            'total' => 'integer',
            'route_distance_km' => 'float',
            'route_eta_minutes' => 'integer',
            'placed_at' => 'datetime',
            'assigned_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
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

    public function isAssignable(): bool
    {
        return in_array($this->status, DeliveryDispatch::DISPATCHABLE_STATUSES, true)
            && empty($this->driver_id)
            && ! $this->isPickup();
    }

    public function isActiveDelivery(): bool
    {
        return in_array($this->status, DeliveryDispatch::ACTIVE_STATUSES, true)
            && ! empty($this->driver_id);
    }

    public function isPickup(): bool
    {
        $address = trim((string) $this->delivery_address);

        return $address === '' || strcasecmp($address, 'Pickup') === 0;
    }

    public function destinationLabel(): string
    {
        return collect([
            $this->delivery_address,
            $this->delivery_city,
        ])->filter()->unique()->implode(', ') ?: 'Address pending';
    }

    public function mapsUrl(): string
    {
        return DeliveryDispatch::mapsUrl($this->destinationLabel());
    }

    public function instructionsText(): string
    {
        $parts = array_filter([
            $this->delivery_instructions,
            $this->notes,
        ]);

        return $parts ? implode(' · ', array_unique($parts)) : 'No special instructions';
    }

    public function itemSummary(): string
    {
        return collect($this->items ?? [])
            ->map(fn (array $item) => ($item['qty'] ?? 1).'× '.($item['name'] ?? 'Item'))
            ->implode(', ');
    }
}
