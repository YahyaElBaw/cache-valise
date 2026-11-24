<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_application_pages_redirect_to_login_when_logged_out(): void
    {
        $routes = [
            route('dashboard'),
            route('product-models.index'),
            route('batches.index'),
            route('charges.index'),
            route('suppliers.index'),
            route('customers.index'),
            route('sales.index'),
            route('payments.index'),
            route('payments.create'),
            route('cash.index'),
            route('reports.show', 'charges'),
            route('activity-logs.index'),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
