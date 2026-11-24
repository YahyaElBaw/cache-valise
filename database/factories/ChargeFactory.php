<?php

namespace Database\Factories;

use App\Enums\ChargeCategory;
use App\Models\Batch;
use App\Models\Charge;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Charge> */
class ChargeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'number' => 'CHG-2026-' . $this->faker->unique()->numerify('####'),
            'charge_date' => now()->toDateString(),
            'batch_id' => Batch::factory(),
            'supplier_id' => null,
            'category' => ChargeCategory::FABRIC,
            'label' => 'Achat matière',
            'quantity' => '1.000',
            'unit' => 'forfait',
            'unit_price' => '100.000',
            'amount' => '100.000',
            'status' => 'active',
        ];
    }
}
