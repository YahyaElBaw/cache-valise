<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Models\Batch;
use App\Models\Charge;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_payment_updates_amount_due(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        Charge::factory()->for($batch)->create(['quantity' => 1, 'unit_price' => 1250]);

        $salesService = app(SalesService::class);
        $sale = $salesService->createSale([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 40, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        $this->post(route('payments.store'), [
            'payment_date' => now()->toDateString(),
            'sale_id' => $sale->id,
            'amount' => '1000.000',
            'method' => PaymentMethod::CASH->value,
        ])->assertRedirect(route('sales.show', $sale));

        $this->assertSame('800.000', $salesService->amountDue($sale));
    }

    public function test_payment_above_amount_due_is_refused(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        $salesService = app(SalesService::class);
        $sale = $salesService->createSale([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 40, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        $response = $this->from(route('payments.create', ['sale_id' => $sale->id]))
            ->post(route('payments.store'), [
                'payment_date' => now()->toDateString(),
                'sale_id' => $sale->id,
                'amount' => '2000.000',
                'method' => PaymentMethod::CASH->value,
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertSame('1800.000', $salesService->amountDue($sale));
    }
}
