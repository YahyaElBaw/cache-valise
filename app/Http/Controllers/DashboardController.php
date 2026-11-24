<?php

namespace App\Http\Controllers;

use App\Enums\ChargeCategory;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Charge;
use App\Models\Sale;
use App\Services\CashService;
use App\Services\SalesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, SalesService $salesService, CashService $cashService): View
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $periodSales = Sale::query()
            ->with('items')
            ->where('status', 'validated')
            ->whereBetween('sale_date', [$from, $to])
            ->get();

        $revenue = '0.000';
        $cogs = '0.000';
        foreach ($periodSales as $sale) {
            $revenue = bcadd($revenue, (string) $sale->total, 3);
            $cogs = bcadd($cogs, $salesService->cogs($sale), 3);
        }
        $grossMargin = bcsub($revenue, $cogs, 3);

        // Total receivables across all validated sales
        $allValidatedSales = Sale::query()
            ->with(['payments' => fn ($q) => $q->where('status', 'valid')->where('direction', 'in')])
            ->where('status', 'validated')
            ->get();

        $receivables = '0.000';
        foreach ($allValidatedSales as $sale) {
            $paid = '0.000';
            foreach ($sale->payments as $p) {
                $paid = bcadd($paid, (string) $p->amount, 3);
            }
            $due = bcsub((string) $sale->total, $paid, 3);
            if (bccomp($due, '0.000', 3) > 0) {
                $receivables = bcadd($receivables, $due, 3);
            }
        }

        $cashBalance = $cashService->balance();

        // Bar chart: Revenue vs Charges for the last 6 months
        $monthsLabels = [];
        $monthlyRevenue = [];
        $monthlyCharges = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::parse($to)->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $monthsLabels[] = $monthStart->translatedFormat('M Y');

            $rev = Sale::query()
                ->where('status', 'validated')
                ->whereBetween('sale_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('total');

            $chg = Charge::query()
                ->where('status', 'active')
                ->whereBetween('charge_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $monthlyRevenue[] = (float) $rev;
            $monthlyCharges[] = (float) $chg;
        }

        // Doughnut chart: Active charges by category in selected period
        $categoryLabels = [];
        $categoryTotals = [];
        foreach (ChargeCategory::cases() as $category) {
            $sum = Charge::query()
                ->where('status', 'active')
                ->where('category', $category->value)
                ->whereBetween('charge_date', [$from, $to])
                ->sum('amount');
            $categoryLabels[] = $category->label();
            $categoryTotals[] = (float) $sum;
        }

        $recentSales = Sale::query()->with('customer')->latest('sale_date')->latest('id')->limit(5)->get();
        $recentBatches = Batch::query()->with('productModel')->latest('production_date')->latest('id')->limit(5)->get();
        $recentLogs = ActivityLog::query()->latest('id')->limit(6)->get();

        return view('dashboard', compact(
            'from',
            'to',
            'revenue',
            'cogs',
            'grossMargin',
            'receivables',
            'cashBalance',
            'monthsLabels',
            'monthlyRevenue',
            'monthlyCharges',
            'categoryLabels',
            'categoryTotals',
            'recentSales',
            'recentBatches',
            'recentLogs'
        ));
    }
}
