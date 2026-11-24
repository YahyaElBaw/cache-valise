<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Charge;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_totals_and_unit_cost_snapshot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        foreach ([800, 250, 150, 50] as $amount) {
            Charge::factory()->for($batch)->create(['quantity' => 1, 'unit_price' => $amount]);
        }

        $customer = Customer::factory()->create();

        $response = $this->post(route('sales.store'), [
            'invoice_number' => 'FAC-2026-0001',
            'sale_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'items' => [
                [
                    'batch_id' => $batch->id,
                    'quantity' => 40,
                    'unit_price' => '45.000',
                    'discount' => '0.000',
                ],
            ],
        ]);

        $sale = Sale::where('invoice_number', 'FAC-2026-0001')->firstOrFail();
        $response->assertRedirect(route('sales.show', $sale));

        $this->assertSame('1800.000', (string) $sale->total);
        $this->assertSame('12.500', (string) $sale->items->first()->unit_cost);

        $salesService = app(SalesService::class);
        $this->assertSame('500.000', $salesService->cogs($sale));
        $this->assertSame('1300.000', $salesService->grossMargin($sale));
    }

    public function test_editing_lot_charges_after_sale_leaves_margin_unchanged(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        $charge = Charge::factory()->for($batch)->create(['quantity' => 1, 'unit_price' => 1250]);

        $salesService = app(SalesService::class);
        $sale = $salesService->createSale([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 40, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        // Modify charge later (now lot cost = 2000 DT => 20 DT/unit)
        $charge->update(['unit_price' => '2000.000']);

        $sale->refresh()->load('items');
        $this->assertSame('12.500', (string) $sale->items->first()->unit_cost);
        $this->assertSame('1300.000', $salesService->grossMargin($sale));
    }

    public function test_sale_is_never_blocked_when_exceeding_remaining_quantity(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 10]);

        $response = $this->post(route('sales.store'), [
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 25, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        $sale = Sale::latest('id')->firstOrFail();
        $response->assertRedirect(route('sales.show', $sale));
        $response->assertSessionHas('warning');
        $this->assertSame(25, $sale->items->first()->quantity);
    }

    public function test_invoice_pdf_renders_and_cannot_cancel_sale_with_valid_payments(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        $sale = app(SalesService::class)->createSale([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 10, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        $pdf = $this->get(route('sales.pdf', $sale));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('content-type'));

        $this->post(route('payments.store'), [
            'payment_date' => now()->toDateString(),
            'sale_id' => $sale->id,
            'amount' => '100.000',
            'method' => 'cash',
        ])->assertRedirect();

        $this->from(route('sales.show', $sale))
            ->post(route('sales.cancel', $sale))
            ->assertSessionHasErrors('sale');

        $payment = $sale->payments()->firstOrFail();
        $this->post(route('payments.cancel', $payment))->assertRedirect();

        $this->post(route('sales.cancel', $sale))->assertRedirect(route('sales.show', $sale));
        $this->assertSame('cancelled', $sale->fresh()->status);
    }
}
