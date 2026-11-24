<?php

namespace App\Models;

use App\Enums\CashMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'moved_at',
        'type',
        'direction',
        'amount',
        'label',
        'payment_id',
        'reverses_id',
    ];

    protected function casts(): array
    {
        return [
            'moved_at' => 'datetime',
            'type' => CashMovementType::class,
            'amount' => 'decimal:3',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function reverses(): BelongsTo
    {
        return $this->belongsTo(CashMovement::class, 'reverses_id');
    }

    public function reversedBy(): HasOne
    {
        return $this->hasOne(CashMovement::class, 'reverses_id');
    }
}
