<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Charge;
use App\Models\Payment;
use App\Models\Sale;
use App\Services\CashService;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $direction = $request->input('direction', '');
        $method = $request->input('method', '');

        $payments = Payment::query()
            ->with(['sale.customer', 'charge.supplier', 'cashMovement'])
            ->when($direction !== '', fn ($q) => $q->where('direction', $direction))
            ->when($method !== '', fn ($q) => $q->where('method', $method))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $methods = PaymentMethod::cases();

        return view('payments.index', compact('payments', 'methods', 'direction', 'method'));
    }

    public function create(Request $request, SalesService $salesService, CashService $cashService): View
    {
        $sale = $request->filled('sale_id') ? Sale::with('customer')->findOrFail($request->input('sale_id')) : null;
        $charge = $request->filled('charge_id') ? Charge::with(['batch', 'supplier'])->findOrFail($request->input('charge_id')) : null;

        $amountDue = '0.000';
        if ($sale) {
            $amountDue = $salesService->amountDue($sale);
        } elseif ($charge) {
            $amountDue = $cashService->chargeDue($charge);
        }

        $openSales = $sale ? collect() : Sale::query()
            ->with('customer')
            ->where('status', 'validated')
            ->latest('id')
            ->get()
            ->filter(fn (Sale $s) => bccomp($salesService->amountDue($s), '0.000', 3) > 0);

        $openCharges = $charge ? collect() : Charge::query()
            ->with(['batch', 'supplier'])
            ->where('status', 'active')
            ->latest('id')
            ->get()
            ->filter(fn (Charge $c) => bccomp($cashService->chargeDue($c), '0.000', 3) > 0);

        $methods = PaymentMethod::cases();
        $idempotencyKey = (string) Str::uuid();
        $cashBalance = $cashService->balance();

        return view('payments.create', compact(
            'sale',
            'charge',
            'amountDue',
            'openSales',
            'openCharges',
            'methods',
            'idempotencyKey',
            'cashBalance'
        ));
    }

    public function store(StorePaymentRequest $request, CashService $cashService): RedirectResponse
    {
        $payment = $cashService->recordPayment($request->validated());

        if ($payment->sale_id) {
            return redirect()->route('sales.show', $payment->sale_id)->with('success', 'Règlement client enregistré.');
        }

        return redirect()->route('charges.show', $payment->charge_id)->with('success', 'Paiement de charge enregistré.');
    }

    public function cancel(Payment $payment, CashService $cashService): RedirectResponse
    {
        $cashService->cancelPayment($payment);

        return back()->with('success', 'Règlement annulé (contre-écriture enregistrée si espèces).');
    }
}
