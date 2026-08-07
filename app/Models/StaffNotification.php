<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class StaffNotification extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'staff_notifications';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'link',
        'read_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function icon(): string
    {
        return match ($this->type) {
            'order' => 'bi-receipt',
            'delivery' => 'bi-truck',
            'promotion' => 'bi-tag',
            'inventory' => 'bi-box-seam',
            'user' => 'bi-person',
            default => 'bi-bell',
        };
    }
}
