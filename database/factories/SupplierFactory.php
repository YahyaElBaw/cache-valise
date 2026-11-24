<?php

namespace Database\Factories;

use App\Enums\ChargeCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Supplier> */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'phone' => $this->faker->numerify('2# ### ###'),
            'address' => $this->faker->address(),
            'type' => $this->faker->randomElement(ChargeCategory::cases()),
        ];
    }
}
