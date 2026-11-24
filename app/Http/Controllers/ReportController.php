<?php

namespace App\Http\Controllers;

use App\Enums\ChargeCategory;
use App\Models\CashMovement;
use App\Models\Charge;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Supplier;
use App\Services\CashService;
use App\Services\SalesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, SalesService $salesService, CashService $cashService): View|Response|StreamedResponse
    {
        return $this->show($request, 'charges', $salesService, $cashService);
    }

    public function show(
        Request $request,
        string $type,
        SalesService $salesService,
        CashService $cashService
    ): View|Response|StreamedResponse {
        $allowedTypes = ['charges', 'sales', 'margin', 'receivables', 'debts', 'cash'];
        if (! in_array($type, $allowedTypes, true)) {
            $type = 'charges';
        }

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());
        $format = $request->input('format', 'html');

        $data = $this->buildReportData($type, $from, $to, $salesService, $cashService);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', [
                'type' => $type,
                'from' => $from,
                'to' => $to,
                'data' => $data,
            ]);

            return $pdf->stream("rapport-{$type}-{$from}-{$to}.pdf");
        }

        if ($format === 'csv') {
            return $this->streamCsv($type, $from, $to, $data);
        }

        return view('reports.index', [
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]);
    }

    protected function buildReportData(
        string $type,
        string $from,
        string $to,
        SalesService $salesService,
        CashService $cashService
    ): array {
        return match ($type) {
            'charges' => $this->chargesReport($from, $to),
            'sales' => $this->salesReport($from, $to, $salesService),
            'margin' => $this->marginReport($from, $to, $salesService),
            'receivables' => $this->receivablesReport($salesService),
            'debts' => $this->debtsReport($cashService),
            'cash' => $this->cashReport($from, $to, $cashService),
        };
    }

    protected function chargesReport(string $from, string $to): array
    {
        $charges = Charge::query()
            ->with(['batch.productModel', 'supplier'])
            ->where('status', 'active')
            ->whereBetween('charge_date', [$from, $to])
            ->orderBy('charge_date')
            ->get();

        $byCategory = [];
        foreach (ChargeCategory::cases() as $cat) {
            $byCategory[$cat->label()] = '0.000';
        }
        $total = '0.000';
        foreach ($charges as $c) {
            $byCategory[$c->category->label()] = bcadd($byCategory[$c->category->label()], (string) $c->amount, 3);
            $total = bcadd($total, (string) $c->amount, 3);
        }

        return compact('charges', 'byCategory', 'total');
    }

    protected function salesReport(string $from, string $to, SalesService $salesService): array
    {
        $sales = Sale::query()
            ->with(['customer', 'items.batch.productModel', 'payments'])
            ->where('status', 'validated')
            ->whereBetween('sale_date', [$from, $to])
            ->orderBy('sale_date')
            ->get();

        $totalRevenue = '0.000';
        $totalPaid = '0.000';
        $totalDue = '0.000';
        foreach ($sales as $sale) {
            $paid = $salesService->amountPaid($sale);
            $due = $salesService->amountDue($sale);
            $sale->setAttribute('paid_amount', $paid);
            $sale->setAttribute('due_amount', $due);
            $totalRevenue = bcadd($totalRevenue, (string) $sale->total, 3);
            $totalPaid = bcadd($totalPaid, $paid, 3);
            $totalDue = bcadd($totalDue, $due, 3);
        }

        return compact('sales', 'totalRevenue', 'totalPaid', 'totalDue');
    }

    protected function marginReport(string $from, string $to, SalesService $salesService): array
    {
        $sales = Sale::query()
            ->with(['customer', 'items.batch'])
            ->where('status', 'validated')
            ->whereBetween('sale_date', [$from, $to])
            ->orderBy('sale_date')
            ->get();

        $totalCa = '0.000';
        $totalCogs = '0.000';
        foreach ($sales as $sale) {
            $cogs = $salesService->cogs($sale);
            $margin = $salesService->grossMargin($sale);
            $sale->setAttribute('cogs_amount', $cogs);
            $sale->setAttribute('margin_amount', $margin);
            $totalCa = bcadd($totalCa, (string) $sale->total, 3);
            $totalCogs = bcadd($totalCogs, $cogs, 3);
        }
        $totalMargin = bcsub($totalCa, $totalCogs, 3);

        return compact('sales', 'totalCa', 'totalCogs', 'totalMargin');
    }

    protected function receivablesReport(SalesService $salesService): array
    {
        $customers = Customer::query()
            ->with(['sales' => fn ($q) => $q->where('status', 'validated')->with('payments')])
            ->orderBy('name')
            ->get();

        $rows = [];
        $totalDue = '0.000';

        foreach ($customers as $customer) {
            $invoiced = '0.000';
            $paid = '0.000';
            foreach ($customer->sales as $sale) {
                $invoiced = bcadd($invoiced, (string) $sale->total, 3);
                $paid = bcadd($paid, $salesService->amountPaid($sale), 3);
            }
            $due = bcsub($invoiced, $paid, 3);
            if (bccomp($invoiced, '0.000', 3) > 0) {
                $rows[] = [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'invoiced' => $invoiced,
                    'paid' => $paid,
                    'due' => $due,
                ];
                $totalDue = bcadd($totalDue, $due, 3);
            }
        }

        // Ventes comptoir non soldées
        $walkInSales = Sale::query()->whereNull('customer_id')->where('status', 'validated')->with('payments')->get();
        $walkInInvoiced = '0.000';
        $walkInPaid = '0.000';
        foreach ($walkInSales as $s) {
            $walkInInvoiced = bcadd($walkInInvoiced, (string) $s->total, 3);
            $walkInPaid = bcadd($walkInPaid, $salesService->amountPaid($s), 3);
        }
        $walkInDue = bcsub($walkInInvoiced, $walkInPaid, 3);
        if (bccomp($walkInDue, '0.000', 3) > 0) {
            $rows[] = [
                'name' => 'Ventes comptoir',
                'phone' => '-',
                'invoiced' => $walkInInvoiced,
                'paid' => $walkInPaid,
                'due' => $walkInDue,
            ];
            $totalDue = bcadd($totalDue, $walkInDue, 3);
        }

        return compact('rows', 'totalDue');
    }

    protected function debtsReport(CashService $cashService): array
    {
        $suppliers = Supplier::query()
            ->with(['charges' => fn ($q) => $q->where('status', 'active')->with('payments')])
            ->orderBy('name')
            ->get();

        $rows = [];
        $totalDebt = '0.000';
        foreach ($suppliers as $supplier) {
            $charged = '0.000';
            $paid = '0.000';
            foreach ($supplier->charges as $charge) {
                $charged = bcadd($charged, (string) $charge->amount, 3);
                $paid = bcadd($paid, $cashService->chargePaid($charge), 3);
            }
            $debt = bcsub($charged, $paid, 3);
            $rows[] = [
                'name' => $supplier->name,
                'type' => $supplier->type->label(),
                'charged' => $charged,
                'paid' => $paid,
                'debt' => $debt,
            ];
            $totalDebt = bcadd($totalDebt, $debt, 3);
        }

        return compact('rows', 'totalDebt');
    }

    protected function cashReport(string $from, string $to, CashService $cashService): array
    {
        $movements = CashMovement::query()
            ->whereBetween('moved_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderBy('moved_at')
            ->orderBy('id')
            ->get();

        $totalIn = '0.000';
        $totalOut = '0.000';
        foreach ($movements as $m) {
            if ($m->direction === 'in') {
                $totalIn = bcadd($totalIn, (string) $m->amount, 3);
            } else {
                $totalOut = bcadd($totalOut, (string) $m->amount, 3);
            }
        }
        $currentBalance = $cashService->balance();

        return compact('movements', 'totalIn', 'totalOut', 'currentBalance');
    }

    protected function streamCsv(string $type, string $from, string $to, array $data): StreamedResponse
    {
        $filename = "rapport-{$type}-{$from}-{$to}.csv";

        return response()->streamDownload(function () use ($type, $data) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($out, "\xEF\xBB\xBF");

            if ($type === 'charges') {
                fputcsv($out, ['Numéro', 'Date', 'Lot', 'Fournisseur', 'Catégorie', 'Libellé', 'Quantité', 'PU (DT)', 'Montant (DT)'], ';');
                foreach ($data['charges'] as $c) {
                    fputcsv($out, [$c->number, $c->charge_date->format('Y-m-d'), $c->batch?->number, $c->supplier?->name ?? '-', $c->category->label(), $c->label, $c->quantity, $c->unit_price, $c->amount], ';');
                }
            } elseif ($type === 'sales') {
                fputcsv($out, ['Facture', 'Date', 'Client', 'Total (DT)', 'Encaissé (DT)', 'Reste dû (DT)'], ';');
                foreach ($data['sales'] as $s) {
                    fputcsv($out, [$s->invoice_number, $s->sale_date->format('Y-m-d'), $s->customer?->name ?? 'Vente comptoir', $s->total, $s->paid_amount, $s->due_amount], ';');
                }
            } elseif ($type === 'margin') {
                fputcsv($out, ['Facture', 'Date', 'Client', 'CA (DT)', 'COGS (DT)', 'Marge brute (DT)'], ';');
                foreach ($data['sales'] as $s) {
                    fputcsv($out, [$s->invoice_number, $s->sale_date->format('Y-m-d'), $s->customer?->name ?? 'Vente comptoir', $s->total, $s->cogs_amount, $s->margin_amount], ';');
                }
            } elseif ($type === 'receivables') {
                fputcsv($out, ['Client', 'Téléphone', 'Total facturé (DT)', 'Total encaissé (DT)', 'Créance restante (DT)'], ';');
                foreach ($data['rows'] as $r) {
                    fputcsv($out, [$r['name'], $r['phone'] ?? '-', $r['invoiced'], $r['paid'], $r['due']], ';');
                }
            } elseif ($type === 'debts') {
                fputcsv($out, ['Fournisseur', 'Type', 'Charges engagées (DT)', 'Payé (DT)', 'Dette restante (DT)'], ';');
                foreach ($data['rows'] as $r) {
                    fputcsv($out, [$r['name'], $r['type'], $r['charged'], $r['paid'], $r['debt']], ';');
                }
            } elseif ($type === 'cash') {
                fputcsv($out, ['Date/Heure', 'Type', 'Sens', 'Libellé', 'Montant (DT)'], ';');
                foreach ($data['movements'] as $m) {
                    fputcsv($out, [$m->moved_at->format('Y-m-d H:i'), $m->type->label(), $m->direction === 'in' ? 'Entrée' : 'Sortie', $m->label, $m->amount], ';');
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
