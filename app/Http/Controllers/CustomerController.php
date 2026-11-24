<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use App\Services\SalesService;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request, SalesService $salesService): View
    {
        $search = trim((string) $request->input('q', ''));
        $customers = Customer::query()
            ->with(['sales' => fn ($q) => $q->where('status', 'validated')->with('payments')])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'salesService', 'search'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create($request->validated());
        ActivityLogger::log('create', Customer::class, $customer->id, "Création du client {$customer->name}.");

        return redirect()->route('customers.index')->with('success', 'Client créé avec succès.');
    }

    public function show(Customer $customer, SalesService $salesService): View
    {
        $customer->load(['sales.payments']);

        $totalInvoiced = '0.000';
        $totalPaid = '0.000';
        foreach ($customer->sales->where('status', 'validated') as $sale) {
            $totalInvoiced = bcadd($totalInvoiced, (string) $sale->total, 3);
            $totalPaid = bcadd($totalPaid, $salesService->amountPaid($sale), 3);
        }
        $amountDue = bcsub($totalInvoiced, $totalPaid, 3);

        return view('customers.show', compact('customer', 'totalInvoiced', 'totalPaid', 'amountDue', 'salesService'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(StoreCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());
        ActivityLogger::log('update', Customer::class, $customer->id, "Mise à jour du client {$customer->name}.");

        return redirect()->route('customers.show', $customer)->with('success', 'Client mis à jour.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->exists()) {
            return back()->with('error', 'Impossible de supprimer un client ayant des factures.');
        }

        $name = $customer->name;
        $customer->delete();
        ActivityLogger::log('delete', Customer::class, null, "Suppression du client {$name}.");

        return redirect()->route('customers.index')->with('success', 'Client supprimé.');
    }
}
