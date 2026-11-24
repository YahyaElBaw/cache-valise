<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Sale;
use App\Services\ProductionCostService;
use App\Services\SalesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request, SalesService $salesService): View
    {
        $status = $request->input('status', '');
        $customerId = $request->input('customer_id', '');

        $sales = Sale::query()
            ->with(['customer', 'payments'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($customerId !== '', fn ($q) => $q->where('customer_id', $customerId))
            ->latest('sale_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::query()->orderBy('name')->get();

        return view('sales.index', compact('sales', 'salesService', 'customers', 'status', 'customerId'));
    }

    public function create(ProductionCostService $costService): View
    {
        $customers = Customer::query()->orderBy('name')->get();
        $batches = Batch::query()
            ->with('productModel')
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('id')
            ->get()
            ->map(function (Batch $batch) use ($costService) {
                $batch->remaining_qty = $costService->remainingQuantity($batch);
                $batch->computed_unit_cost = $costService->unitCost($batch);
                return $batch;
            });

        $suggestedNumber = 'FAC-' . now()->format('Y') . '-' . str_pad((string) (Sale::max('id') + 1), 4, '0', STR_PAD_LEFT);

        return view('sales.create', compact('customers', 'batches', 'suggestedNumber'));
    }

    public function store(StoreSaleRequest $request, SalesService $salesService): RedirectResponse
    {
        $sale = $salesService->createSale($request->validated());

        $redirect = redirect()->route('sales.show', $sale)->with('success', "Facture {$sale->invoice_number} enregistrée.");
        if (! empty($salesService->lastWarnings)) {
            $redirect->with('warning', implode(' ', $salesService->lastWarnings));
        }

        return $redirect;
    }

    public function show(Sale $sale, SalesService $salesService): View
    {
        $sale->load(['customer', 'items.batch.productModel', 'payments.cashMovement']);

        $amountPaid = $salesService->amountPaid($sale);
        $amountDue = $salesService->amountDue($sale);
        $cogs = $salesService->cogs($sale);
        $grossMargin = $salesService->grossMargin($sale);
        $methods = PaymentMethod::cases();

        return view('sales.show', compact('sale', 'amountPaid', 'amountDue', 'cogs', 'grossMargin', 'methods'));
    }

    public function pdf(Sale $sale, SalesService $salesService): Response
    {
        $sale->load(['customer', 'items.batch.productModel', 'payments']);
        $amountPaid = $salesService->amountPaid($sale);
        $amountDue = $salesService->amountDue($sale);

        $pdf = Pdf::loadView('pdf.invoice', compact('sale', 'amountPaid', 'amountDue'));

        return $pdf->stream("facture-{$sale->invoice_number}.pdf");
    }

    public function cancel(Sale $sale, SalesService $salesService): RedirectResponse
    {
        $salesService->cancelSale($sale);

        return redirect()->route('sales.show', $sale)->with('success', "Facture {$sale->invoice_number} annulée.");
    }
}
