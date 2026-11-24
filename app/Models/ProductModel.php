<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'designation',
        'dimensions',
        'description',
        'suggested_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'suggested_price' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
