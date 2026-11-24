<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('5# ### ###'),
            'address' => $this->faker->city(),
            'email' => $this->faker->safeEmail(),
        ];
    }
}
