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

    public const STATUS_RECEIVED = 'received';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_BAKING = 'baking';

    public const STATUS_READY = 'ready';

    public const STATUS_OUT = 'out_for_delivery';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    /** Kitchen / fulfillment pipeline (excluding cancelled). */
    public const STATUS_FLOW = [
        self::STATUS_RECEIVED,
        self::STATUS_PREPARING,
        self::STATUS_BAKING,
        self::STATUS_READY,
        self::STATUS_OUT,
        self::STATUS_DELIVERED,
    ];

    /** @var list<string> */
    public const OPEN_STATUSES = [
        self::STATUS_RECEIVED,
        'pending',
        self::STATUS_PREPARING,
        self::STATUS_BAKING,
        self::STATUS_READY,
        self::STATUS_OUT,
    ];

    /** Statuses shown on the Kitchen Display System board. */
    public const KITCHEN_STATUSES = [
        self::STATUS_RECEIVED,
        'pending',
        self::STATUS_PREPARING,
        self::STATUS_BAKING,
    ];

    public const ITEM_KDS_PENDING = 'pending';

    public const ITEM_KDS_BAKING = 'baking';

    public const ITEM_KDS_COMPLETED = 'completed';

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
        'fulfillment_type',
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
        'cancelled_at',
        'status_updated_at',
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
            'cancelled_at' => 'datetime',
            'status_updated_at' => 'datetime',
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

    public function normalizedStatus(): string
    {
        return $this->status === 'pending' ? self::STATUS_RECEIVED : (string) $this->status;
    }

    public function statusLabel(): string
    {
        return match ($this->normalizedStatus()) {
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_PREPARING => 'Preparing',
            self::STATUS_BAKING => 'Baking',
            self::STATUS_READY => 'Ready',
            self::STATUS_OUT => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->normalizedStatus()) {
            self::STATUS_DELIVERED => 'success',
            self::STATUS_PREPARING, self::STATUS_BAKING, self::STATUS_READY => 'warning',
            self::STATUS_OUT => 'primary',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function fulfillmentType(): string
    {
        if (in_array($this->fulfillment_type, ['pickup', 'delivery'], true)) {
            return $this->fulfillment_type;
        }

        return $this->isPickup() ? 'pickup' : 'delivery';
    }

    public function fulfillmentLabel(): string
    {
        return $this->fulfillmentType() === 'pickup' ? 'Pickup' : 'Delivery';
    }

    public function nextStatus(): ?string
    {
        $current = $this->normalizedStatus();

        if ($current === self::STATUS_CANCELLED || $current === self::STATUS_DELIVERED) {
            return null;
        }

        // Pickup orders skip out_for_delivery.
        if ($current === self::STATUS_READY && $this->fulfillmentType() === 'pickup') {
            return self::STATUS_DELIVERED;
        }

        $index = array_search($current, self::STATUS_FLOW, true);
        if ($index === false || $index >= count(self::STATUS_FLOW) - 1) {
            return null;
        }

        return self::STATUS_FLOW[$index + 1];
    }

    public function nextStatusLabel(): ?string
    {
        $next = $this->nextStatus();

        return $next ? (new static(['status' => $next]))->statusLabel() : null;
    }

    public function canAdvance(): bool
    {
        return $this->nextStatus() !== null;
    }

    /** Cancel/modify only before kitchen starts preparation. */
    public function canModifyOrCancel(): bool
    {
        return in_array($this->normalizedStatus(), [self::STATUS_RECEIVED, 'pending'], true);
    }

    public function isAssignable(): bool
    {
        return in_array($this->normalizedStatus(), DeliveryDispatch::DISPATCHABLE_STATUSES, true)
            && empty($this->driver_id)
            && $this->fulfillmentType() === 'delivery';
    }

    public function isActiveDelivery(): bool
    {
        return in_array($this->normalizedStatus(), DeliveryDispatch::ACTIVE_STATUSES, true)
            && ! empty($this->driver_id);
    }

    public function isPickup(): bool
    {
        if ($this->fulfillment_type === 'pickup') {
            return true;
        }

        if ($this->fulfillment_type === 'delivery') {
            return false;
        }

        $address = trim((string) $this->delivery_address);

        return $address === '' || strcasecmp($address, 'Pickup') === 0;
    }

    public function destinationLabel(): string
    {
        if ($this->fulfillmentType() === 'pickup') {
            return 'Store pickup';
        }

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

    public function isInKitchenQueue(): bool
    {
        return in_array($this->normalizedStatus(), [
            self::STATUS_RECEIVED,
            self::STATUS_PREPARING,
            self::STATUS_BAKING,
        ], true);
    }

    /**
     * Line items enriched for KDS: base name + highlighted customizations.
     *
     * @return list<array{
     *     index: int,
     *     qty: int,
     *     price: int,
     *     base_name: string,
     *     display_name: string,
     *     size: ?string,
     *     crust: ?string,
     *     sauce: ?string,
     *     toppings: list<string>,
     *     mods: list<string>,
     *     has_mods: bool,
     *     kds_status: string,
     *     notes: ?string
     * }>
     */
    public function kitchenItems(): array
    {
        $items = array_values($this->items ?? []);

        return collect($items)->values()->map(function (array $item, int $index) {
            $parsed = self::parseItemCustomizations($item);

            return [
                'index' => $index,
                'qty' => max(1, (int) ($item['qty'] ?? 1)),
                'price' => (int) ($item['price'] ?? 0),
                'base_name' => $parsed['base_name'],
                'display_name' => (string) ($item['name'] ?? $parsed['base_name']),
                'size' => $parsed['size'],
                'crust' => $parsed['crust'],
                'sauce' => $parsed['sauce'],
                'toppings' => $parsed['toppings'],
                'mods' => $parsed['mods'],
                'has_mods' => $parsed['mods'] !== [],
                'kds_status' => in_array($item['kds_status'] ?? self::ITEM_KDS_PENDING, [
                    self::ITEM_KDS_PENDING,
                    self::ITEM_KDS_BAKING,
                    self::ITEM_KDS_COMPLETED,
                ], true) ? ($item['kds_status'] ?? self::ITEM_KDS_PENDING) : self::ITEM_KDS_PENDING,
                'notes' => isset($item['notes']) ? (string) $item['notes'] : null,
            ];
        })->all();
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{
     *     base_name: string,
     *     size: ?string,
     *     crust: ?string,
     *     sauce: ?string,
     *     toppings: list<string>,
     *     mods: list<string>
     * }
     */
    public static function parseItemCustomizations(array $item): array
    {
        $rawName = (string) ($item['name'] ?? 'Item');
        $baseName = (string) ($item['base_name'] ?? $rawName);
        $size = isset($item['size']) && $item['size'] !== '' ? (string) $item['size'] : null;
        $crust = isset($item['crust']) && $item['crust'] !== '' ? (string) $item['crust'] : null;
        $sauce = isset($item['sauce']) && $item['sauce'] !== '' ? (string) $item['sauce'] : null;
        $toppings = [];

        if (! empty($item['toppings']) && is_array($item['toppings'])) {
            foreach ($item['toppings'] as $topping) {
                if (is_string($topping) && $topping !== '') {
                    $toppings[] = $topping;
                } elseif (is_array($topping) && ! empty($topping['name'])) {
                    $toppings[] = (string) $topping['name'];
                }
            }
        }

        // Legacy checkout flattened customizations into "Name [Size | Crust | Toppings]".
        if (($size === null && $crust === null && $toppings === []) && preg_match('/^(.*?)\s*\[(.*)\]\s*$/', $rawName, $matches)) {
            $baseName = trim($matches[1]) ?: $baseName;
            $parts = array_values(array_filter(array_map('trim', explode('|', $matches[2]))));

            if (isset($parts[0]) && $parts[0] !== '') {
                $size = $parts[0];
            }
            if (isset($parts[1]) && $parts[1] !== '') {
                $crust = $parts[1];
            }
            if (isset($parts[2]) && $parts[2] !== '') {
                $toppings = array_values(array_filter(array_map('trim', explode(',', $parts[2]))));
            }
            if (isset($parts[3]) && $parts[3] !== '') {
                $sauce = $parts[3];
            }
        } elseif ($baseName === $rawName && str_contains($rawName, '[')) {
            $baseName = trim(preg_replace('/\s*\[.*\]\s*$/', '', $rawName) ?? $rawName);
        }

        $mods = array_values(array_filter([
            $size,
            $crust,
            $sauce,
            ...array_map(fn (string $t) => '+ '.$t, $toppings),
        ]));

        return [
            'base_name' => $baseName !== '' ? $baseName : 'Item',
            'size' => $size,
            'crust' => $crust,
            'sauce' => $sauce,
            'toppings' => $toppings,
            'mods' => $mods,
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public static function flowSteps(): array
    {
        return [
            ['key' => self::STATUS_RECEIVED, 'label' => 'Received'],
            ['key' => self::STATUS_PREPARING, 'label' => 'Preparing'],
            ['key' => self::STATUS_BAKING, 'label' => 'Baking'],
            ['key' => self::STATUS_READY, 'label' => 'Ready'],
            ['key' => self::STATUS_OUT, 'label' => 'Out for Delivery'],
            ['key' => self::STATUS_DELIVERED, 'label' => 'Delivered'],
        ];
    }
}
