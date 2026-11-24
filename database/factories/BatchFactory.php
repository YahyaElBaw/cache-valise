<?php

namespace Database\Factories;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Batch> */
class BatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'number' => 'LOT-2026-' . $this->faker->unique()->numerify('###'),
            'product_model_id' => ProductModel::factory(),
            'quantity' => 100,
            'production_date' => now()->toDateString(),
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->toDateString(),
            'status' => BatchStatus::PREPARING,
            'notes' => null,
        ];
    }
}
