<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Charge;
use App\Services\ProductionCostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_cost_is_total_charges_divided_by_quantity(): void
    {
        $batch = Batch::factory()->create(['quantity' => 100]);
        foreach ([800, 250, 150, 50] as $amount) {
            Charge::factory()->for($batch)->create([
                'quantity' => 1,
                'unit_price' => $amount,
            ]);
        }

        $service = app(ProductionCostService::class);

        $this->assertSame('1250.000', $service->totalCost($batch));
        $this->assertSame('12.500', $service->unitCost($batch));
    }

    public function test_cancelled_charges_are_excluded_from_lot_cost(): void
    {
        $batch = Batch::factory()->create(['quantity' => 100]);
        Charge::factory()->for($batch)->create(['quantity' => 1, 'unit_price' => 1000, 'status' => 'active']);
        Charge::factory()->for($batch)->create(['quantity' => 1, 'unit_price' => 250, 'status' => 'cancelled']);

        $service = app(ProductionCostService::class);

        $this->assertSame('1000.000', $service->totalCost($batch));
        $this->assertSame('10.000', $service->unitCost($batch));
    }
}
