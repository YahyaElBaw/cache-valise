<?php

namespace App\Services;

use App\Models\Batch;

class ProductionCostService
{
    public function totalCost(Batch $batch): string
    {
        $charges = $batch->charges()
            ->where('status', 'active')
            ->get(['amount']);

        $total = '0.000';
        foreach ($charges as $charge) {
            $total = bcadd($total, (string) $charge->amount, 3);
        }

        return $total;
    }

    public function unitCost(Batch $batch): string
    {
        if ($batch->quantity <= 0) {
            return '0.000';
        }

        return bcdiv($this->totalCost($batch), (string) $batch->quantity, 3);
    }

    public function soldQuantity(Batch $batch): int
    {
        return (int) $batch->saleItems()
            ->whereHas('sale', fn ($q) => $q->where('status', 'validated'))
            ->sum('quantity');
    }

    public function remainingQuantity(Batch $batch): int
    {
        return $batch->quantity - $this->soldQuantity($batch);
    }
}
