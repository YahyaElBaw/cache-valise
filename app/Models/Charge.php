<?php

namespace App\Models;

use App\Enums\ChargeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'charge_date',
        'batch_id',
        'supplier_id',
        'category',
        'label',
        'quantity',
        'unit',
        'unit_price',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'charge_date' => 'date',
            'category' => ChargeCategory::class,
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:3',
            'amount' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Charge $charge) {
            $charge->amount = bcmul((string) $charge->quantity, (string) $charge->unit_price, 3);
        });
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function validPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('status', 'valid');
    }
}
