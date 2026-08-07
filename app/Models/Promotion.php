<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'promotions';

    public const TYPE_CARD = 'card';

    public const TYPE_BANNER = 'banner';

    public const DISCOUNT_NONE = 'none';

    public const DISCOUNT_PERCENT = 'percent';

    public const DISCOUNT_FIXED = 'fixed';

    public const DISCOUNT_FREE_DELIVERY = 'free_delivery';

    /** @var list<string> */
    public const STYLES = ['style_1', 'style_2', 'style_3', 'style_4'];

    /** @var list<string> */
    public const DISCOUNT_TYPES = [
        self::DISCOUNT_NONE,
        self::DISCOUNT_PERCENT,
        self::DISCOUNT_FIXED,
        self::DISCOUNT_FREE_DELIVERY,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'icon',
        'button_text',
        'button_link',
        'promo_code',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_order_amount',
        'first_order_only',
        'usage_limit',
        'used_count',
        'starts_at',
        'ends_at',
        'style',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_value' => 'float',
            'max_discount' => 'integer',
            'min_order_amount' => 'integer',
            'first_order_only' => 'boolean',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function cardClass(): string
    {
        return match ($this->style) {
            'style_2' => 'pf-offer-2',
            'style_3' => 'pf-offer-3',
            'style_4' => 'pf-offer-4',
            default => 'pf-offer-1',
        };
    }

    public function styleLabel(): string
    {
        return match ($this->style) {
            'style_1' => 'Coral / Orange',
            'style_2' => 'Dark / Red',
            'style_3' => 'Gold / Warm',
            'style_4' => 'Berry / Pink',
            default => 'Default',
        };
    }

    public function typeLabel(): string
    {
        return $this->type === self::TYPE_BANNER ? 'Banner' : 'Offer card';
    }

    public function discountLabel(): string
    {
        $label = match ($this->discount_type) {
            self::DISCOUNT_PERCENT => rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'% off',
            self::DISCOUNT_FIXED => 'Rs. '.number_format((int) $this->discount_value).' off',
            self::DISCOUNT_FREE_DELIVERY => 'Free delivery'
                .($this->min_order_amount ? ' over Rs. '.number_format((int) $this->min_order_amount) : ''),
            default => 'Display only',
        };

        if ($this->discount_type === self::DISCOUNT_PERCENT && (int) $this->max_discount > 0) {
            $label .= ' (max Rs. '.number_format((int) $this->max_discount).')';
        }

        return $label;
    }

    public function isCheckoutDiscount(): bool
    {
        return in_array($this->discount_type, [
            self::DISCOUNT_PERCENT,
            self::DISCOUNT_FIXED,
            self::DISCOUNT_FREE_DELIVERY,
        ], true);
    }

    public function isWithinSchedule(): bool
    {
        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function hasUsesRemaining(): bool
    {
        $limit = (int) ($this->usage_limit ?? 0);

        if ($limit <= 0) {
            return true;
        }

        return (int) ($this->used_count ?? 0) < $limit;
    }

    public function isCurrentlyValid(): bool
    {
        return (bool) $this->is_active
            && $this->isWithinSchedule()
            && $this->hasUsesRemaining();
    }

    public function scheduleLabel(): string
    {
        if (! $this->starts_at && ! $this->ends_at) {
            return 'Always';
        }

        $from = $this->starts_at ? $this->starts_at->format('M j') : 'Now';
        $to = $this->ends_at ? $this->ends_at->format('M j, Y') : '∞';

        return $from.' → '.$to;
    }
}
