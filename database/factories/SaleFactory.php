<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sale> */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_number' => 'FAC-2026-' . $this->faker->unique()->numerify('####'),
            'sale_date' => now()->toDateString(),
            'customer_id' => Customer::factory(),
            'subtotal' => '1800.000',
            'discount_total' => '0.000',
            'total' => '1800.000',
            'status' => 'validated',
        ];
    }
}
