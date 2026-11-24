<?php

namespace App\Http\Controllers;

use App\Enums\BatchStatus;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\ProductModel;
use App\Services\CashService;
use App\Services\ProductionCostService;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(Request $request, ProductionCostService $costService): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status', '');

        $batches = Batch::query()
            ->with(['productModel', 'charges'])
            ->when($search !== '', fn ($q) => $q->where('number', 'like', "%{$search}%"))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('production_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $statuses = BatchStatus::cases();

        return view('batches.index', compact('batches', 'costService', 'search', 'status', 'statuses'));
    }

    public function create(): View
    {
        $models = ProductModel::query()->where('is_active', true)->orderBy('reference')->get();
        $statuses = BatchStatus::cases();

        return view('batches.create', compact('models', 'statuses'));
    }

    public function store(StoreBatchRequest $request): RedirectResponse
    {
        $batch = Batch::create($request->validated());
        ActivityLogger::log('create', Batch::class, $batch->id, "Création du lot {$batch->number} ({$batch->quantity} unités).");

        return redirect()->route('batches.show', $batch)->with('success', 'Lot créé avec succès.');
    }

    public function show(Batch $batch, ProductionCostService $costService, CashService $cashService): View
    {
        $batch->load([
            'productModel',
            'charges.supplier',
            'charges.payments',
            'saleItems.sale.customer',
        ]);

        $totalCost = $costService->totalCost($batch);
        $unitCost = $costService->unitCost($batch);
        $soldQty = $costService->soldQuantity($batch);
        $remainingQty = $costService->remainingQuantity($batch);

        return view('batches.show', compact(
            'batch',
            'totalCost',
            'unitCost',
            'soldQty',
            'remainingQty',
            'cashService'
        ));
    }

    public function edit(Batch $batch): View
    {
        $models = ProductModel::query()->orderBy('reference')->get();
        $statuses = BatchStatus::cases();

        return view('batches.edit', compact('batch', 'models', 'statuses'));
    }

    public function update(UpdateBatchRequest $request, Batch $batch): RedirectResponse
    {
        $batch->update($request->validated());
        ActivityLogger::log('update', Batch::class, $batch->id, "Mise à jour du lot {$batch->number}.");

        return redirect()->route('batches.show', $batch)->with('success', 'Lot mis à jour.');
    }

    public function destroy(Batch $batch): RedirectResponse
    {
        if ($batch->charges()->exists() || $batch->saleItems()->exists()) {
            return back()->with('error', 'Impossible de supprimer un lot ayant des charges ou des ventes.');
        }

        $num = $batch->number;
        $batch->delete();
        ActivityLogger::log('delete', Batch::class, null, "Suppression du lot {$num}.");

        return redirect()->route('batches.index')->with('success', 'Lot supprimé.');
    }
}
