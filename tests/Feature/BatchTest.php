<?php

namespace Tests\Feature;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\ProductModel;
use App\Models\User;
use App\Services\ProductionCostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_lot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('product-models.store'), [
            'reference' => 'CV002',
            'designation' => 'Cache-valise moyen',
            'suggested_price' => '45.000',
            'is_active' => true,
        ])->assertRedirect(route('product-models.index'));

        $model = ProductModel::where('reference', 'CV002')->firstOrFail();

        $response = $this->post(route('batches.store'), [
            'number' => 'LOT-2026-001',
            'product_model_id' => $model->id,
            'quantity' => 100,
            'production_date' => now()->toDateString(),
            'status' => BatchStatus::PREPARING->value,
        ]);

        $batch = Batch::where('number', 'LOT-2026-001')->firstOrFail();
        $response->assertRedirect(route('batches.show', $batch));

        $this->assertSame(BatchStatus::PREPARING, $batch->status);
        $this->assertSame(100, app(ProductionCostService::class)->remainingQuantity($batch));
    }
}
