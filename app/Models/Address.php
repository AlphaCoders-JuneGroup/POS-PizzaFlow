<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'addresses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'label',
        'contact_name',
        'phone',
        'address_line',
        'city',
        'postal_code',
        'landmark',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formatted(): string
    {
        return collect([
            $this->address_line,
            $this->city,
            $this->postal_code,
        ])->filter()->implode(', ');
    }
}
