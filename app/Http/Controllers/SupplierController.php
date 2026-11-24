<?php

namespace App\Http\Controllers;

use App\Enums\ChargeCategory;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use App\Services\CashService;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request, CashService $cashService): View
    {
        $search = trim((string) $request->input('q', ''));
        $suppliers = Supplier::query()
            ->with(['charges' => fn ($q) => $q->where('status', 'active')->with('payments')])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'cashService', 'search'));
    }

    public function create(): View
    {
        $types = ChargeCategory::cases();

        return view('suppliers.create', compact('types'));
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($request->validated());
        ActivityLogger::log('create', Supplier::class, $supplier->id, "Création du fournisseur {$supplier->name}.");

        return redirect()->route('suppliers.index')->with('success', 'Fournisseur créé avec succès.');
    }

    public function show(Supplier $supplier, CashService $cashService): View
    {
        $supplier->load(['charges.batch', 'charges.payments']);

        $totalCharged = '0.000';
        $totalPaid = '0.000';
        foreach ($supplier->charges->where('status', 'active') as $charge) {
            $totalCharged = bcadd($totalCharged, (string) $charge->amount, 3);
            $totalPaid = bcadd($totalPaid, $cashService->chargePaid($charge), 3);
        }
        $debt = bcsub($totalCharged, $totalPaid, 3);

        return view('suppliers.show', compact('supplier', 'totalCharged', 'totalPaid', 'debt', 'cashService'));
    }

    public function edit(Supplier $supplier): View
    {
        $types = ChargeCategory::cases();

        return view('suppliers.edit', compact('supplier', 'types'));
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());
        ActivityLogger::log('update', Supplier::class, $supplier->id, "Mise à jour du fournisseur {$supplier->name}.");

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->charges()->exists()) {
            return back()->with('error', 'Impossible de supprimer un fournisseur lié à des charges.');
        }

        $name = $supplier->name;
        $supplier->delete();
        ActivityLogger::log('delete', Supplier::class, null, "Suppression du fournisseur {$name}.");

        return redirect()->route('suppliers.index')->with('success', 'Fournisseur supprimé.');
    }
}
