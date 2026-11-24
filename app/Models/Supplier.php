<?php

namespace App\Models;

use App\Enums\ChargeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => ChargeCategory::class,
        ];
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }
}
