<?php

namespace Database\Factories;

use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductModel> */
class ProductModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => 'CV' . $this->faker->unique()->numerify('###'),
            'designation' => 'Cache-valise ' . $this->faker->word(),
            'dimensions' => '65x45x25 cm',
            'description' => $this->faker->sentence(),
            'suggested_price' => '45.000',
            'is_active' => true,
        ];
    }
}
