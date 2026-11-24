<?php

namespace Tests\Feature;

use App\Enums\CashMovementType;
use App\Enums\PaymentMethod;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\CashMovement;
use App\Models\Charge;
use App\Models\Payment;
use App\Models\User;
use App\Services\CashService;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CashTest extends TestCase
{
    use RefreshDatabase;

    public function test_entry_equals_payment_amount_not_invoice_total(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        $sale = app(SalesService::class)->createSale([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 40, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        app(CashService::class)->recordPayment([
            'payment_date' => now()->toDateString(),
            'sale_id' => $sale->id,
            'amount' => '1000.000',
            'method' => PaymentMethod::CASH,
        ]);

        $this->assertDatabaseCount('cash_movements', 1);
        $this->assertSame('1000.000', app(CashService::class)->balance());
    }

    public function test_supplier_cash_payment_creates_cash_exit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cashService = app(CashService::class);
        $cashService->addMovement([
            'type' => CashMovementType::OPENING,
            'amount' => '1000.000',
            'label' => 'Solde initial',
        ]);

        $charge = Charge::factory()->create(['quantity' => 1, 'unit_price' => '500.000']);

        $this->post(route('payments.store'), [
            'payment_date' => now()->toDateString(),
            'charge_id' => $charge->id,
            'amount' => '300.000',
            'method' => PaymentMethod::CASH->value,
        ])->assertRedirect(route('charges.show', $charge));

        $this->assertSame('700.000', $cashService->balance());
        $this->assertDatabaseHas('cash_movements', [
            'type' => CashMovementType::CHARGE_PAYMENT->value,
            'direction' => 'out',
            'amount' => '300.000',
        ]);
    }

    public function test_transfer_skips_cash(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        $sale = app(SalesService::class)->createSale([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 40, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        $cashService = app(CashService::class);
        $cashService->recordPayment([
            'payment_date' => now()->toDateString(),
            'sale_id' => $sale->id,
            'amount' => '500.000',
            'method' => PaymentMethod::TRANSFER,
            'reference' => 'VIR-001',
        ]);

        $this->assertDatabaseCount('cash_movements', 0);
        $this->assertSame('0.000', $cashService->balance());
    }

    public function test_no_double_entry_on_duplicate_submission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        $sale = app(SalesService::class)->createSale([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 40, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        $key = (string) Str::uuid();
        $payload = [
            'payment_date' => now()->toDateString(),
            'sale_id' => $sale->id,
            'amount' => '1000.000',
            'method' => PaymentMethod::CASH->value,
            'idempotency_key' => $key,
        ];

        $this->post(route('payments.store'), $payload);
        $this->post(route('payments.store'), $payload);

        // Also verify calling createCashMovementForPayment twice for the same Payment produces 1 row
        $payment = Payment::firstOrFail();
        app(CashService::class)->createCashMovementForPayment($payment);

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('cash_movements', 1);
        $this->assertSame('1000.000', app(CashService::class)->balance());
    }

    public function test_cancel_creates_reversal_and_activity_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $batch = Batch::factory()->create(['quantity' => 100]);
        $sale = app(SalesService::class)->createSale([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['batch_id' => $batch->id, 'quantity' => 40, 'unit_price' => '45.000', 'discount' => '0.000'],
            ],
        ]);

        $cashService = app(CashService::class);
        $payment = $cashService->recordPayment([
            'payment_date' => now()->toDateString(),
            'sale_id' => $sale->id,
            'amount' => '1000.000',
            'method' => PaymentMethod::CASH,
        ]);

        $this->post(route('payments.cancel', $payment))->assertRedirect();

        $payment->refresh();
        $this->assertSame('cancelled', $payment->status);
        $this->assertDatabaseCount('cash_movements', 2);
        $this->assertSame('0.000', $cashService->balance());

        $originalMovement = CashMovement::where('payment_id', $payment->id)->firstOrFail();
        $this->assertDatabaseHas('cash_movements', [
            'type' => CashMovementType::REVERSAL->value,
            'direction' => 'out',
            'amount' => '1000.000',
            'reverses_id' => $originalMovement->id,
        ]);

        $this->assertTrue(
            ActivityLog::where('action', 'cancel')
                ->where('subject_type', Payment::class)
                ->where('subject_id', $payment->id)
                ->exists()
        );
    }

    public function test_cash_exit_above_balance_is_refused(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cashService = app(CashService::class);
        $cashService->addMovement([
            'type' => CashMovementType::OPENING,
            'amount' => '200.000',
            'label' => 'Solde initial',
        ]);

        $charge = Charge::factory()->create(['quantity' => 1, 'unit_price' => '500.000']);

        $response = $this->from(route('payments.create', ['charge_id' => $charge->id]))
            ->post(route('payments.store'), [
                'payment_date' => now()->toDateString(),
                'charge_id' => $charge->id,
                'amount' => '300.000',
                'method' => PaymentMethod::CASH->value,
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertSame('200.000', $cashService->balance());
    }
}
