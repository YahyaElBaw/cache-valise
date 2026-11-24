<?php

namespace App\Http\Controllers;

use App\Enums\ChargeCategory;
use App\Http\Requests\StoreChargeRequest;
use App\Http\Requests\UpdateChargeRequest;
use App\Models\Batch;
use App\Models\Charge;
use App\Models\Supplier;
use App\Services\CashService;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChargeController extends Controller
{
    public function index(Request $request, CashService $cashService): View
    {
        $batchId = $request->input('batch_id');
        $category = $request->input('category');
        $from = $request->input('from');
        $to = $request->input('to');

        $charges = Charge::query()
            ->with(['batch.productModel', 'supplier', 'payments'])
            ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($from, fn ($q) => $q->whereDate('charge_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('charge_date', '<=', $to))
            ->latest('charge_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $batches = Batch::query()->orderByDesc('id')->get();
        $categories = ChargeCategory::cases();

        return view('charges.index', compact('charges', 'batches', 'categories', 'cashService', 'batchId', 'category', 'from', 'to'));
    }

    public function create(Request $request): View
    {
        $batches = Batch::query()->with('productModel')->where('status', '!=', 'cancelled')->orderByDesc('id')->get();
        $suppliers = Supplier::query()->orderBy('name')->get();
        $categories = ChargeCategory::cases();
        $selectedBatchId = $request->input('batch_id');

        $suggestedNumber = 'CHG-' . now()->format('Y') . '-' . str_pad((string) (Charge::max('id') + 1), 4, '0', STR_PAD_LEFT);

        return view('charges.create', compact('batches', 'suppliers', 'categories', 'selectedBatchId', 'suggestedNumber'));
    }

    public function store(StoreChargeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = 'active';
        $charge = Charge::create($data);

        ActivityLogger::log('create', Charge::class, $charge->id, "Enregistrement de la charge {$charge->number} (" . money($charge->amount) . ") sur le lot {$charge->batch->number}.");

        return redirect()->route('batches.show', $charge->batch_id)->with('success', 'Charge enregistrée avec succès.');
    }

    public function show(Charge $charge, CashService $cashService): View
    {
        $charge->load(['batch.productModel', 'supplier', 'payments.cashMovement']);

        return view('charges.show', compact('charge', 'cashService'));
    }

    public function edit(Charge $charge): View
    {
        $batches = Batch::query()->with('productModel')->where('status', '!=', 'cancelled')->orderByDesc('id')->get();
        $suppliers = Supplier::query()->orderBy('name')->get();
        $categories = ChargeCategory::cases();

        return view('charges.edit', compact('charge', 'batches', 'suppliers', 'categories'));
    }

    public function update(UpdateChargeRequest $request, Charge $charge): RedirectResponse
    {
        if ($charge->status === 'cancelled') {
            return back()->with('error', 'Une charge annulée ne peut pas être modifiée.');
        }

        $charge->update($request->validated());
        ActivityLogger::log('update', Charge::class, $charge->id, "Mise à jour de la charge {$charge->number} (" . money($charge->amount) . ').');

        return redirect()->route('batches.show', $charge->batch_id)->with('success', 'Charge mise à jour.');
    }

    public function cancel(Charge $charge): RedirectResponse
    {
        if ($charge->status === 'cancelled') {
            return back()->with('error', 'Cette charge est déjà annulée.');
        }

        if ($charge->validPayments()->exists()) {
            return back()->with('error', "Impossible d'annuler une charge ayant des paiements valides. Annulez d'abord les paiements.");
        }

        $charge->update(['status' => 'cancelled']);
        ActivityLogger::log('cancel', Charge::class, $charge->id, "Annulation de la charge {$charge->number}.");

        return back()->with('success', 'Charge annulée.');
    }
}
