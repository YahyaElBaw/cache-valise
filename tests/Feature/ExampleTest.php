<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_redirects_guests_and_renders_all_screens_when_authenticated(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user);
        $this->seed(DemoDataSeeder::class);

        $urls = [
            route('dashboard'),
            route('product-models.index'),
            route('product-models.create'),
            route('batches.index'),
            route('batches.create'),
            route('charges.index'),
            route('charges.create'),
            route('suppliers.index'),
            route('suppliers.create'),
            route('customers.index'),
            route('customers.create'),
            route('sales.index'),
            route('sales.create'),
            route('payments.index'),
            route('payments.create'),
            route('cash.index'),
            route('reports.show', 'charges'),
            route('activity-logs.index'),
            route('profile.edit'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }
}
