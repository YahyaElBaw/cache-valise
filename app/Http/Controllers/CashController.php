<?php

namespace App\Http\Controllers;

use App\Enums\CashMovementType;
use App\Http\Requests\StoreCashMovementRequest;
use App\Models\CashMovement;
use App\Services\CashService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CashController extends Controller
{
    public function index(CashService $cashService): View
    {
        $movements = CashMovement::query()
            ->with(['payment.sale', 'payment.charge', 'reverses'])
            ->orderBy('moved_at')
            ->orderBy('id')
            ->get();

        // Compute running balance chronologically
        $running = '0.000';
        $rows = [];
        foreach ($movements as $movement) {
            $running = $movement->direction === 'in'
                ? bcadd($running, (string) $movement->amount, 3)
                : bcsub($running, (string) $movement->amount, 3);

            $rows[] = [
                'movement' => $movement,
                'running_balance' => $running,
            ];
        }

        $rows = array_reverse($rows);
        $currentBalance = $cashService->balance();
        $hasOpening = CashMovement::query()->where('type', CashMovementType::OPENING)->exists();
        $manualTypes = array_filter(
            CashMovementType::manualCases(),
            fn (CashMovementType $t) => $t !== CashMovementType::OPENING || ! $hasOpening
        );

        return view('cash.index', compact('rows', 'currentBalance', 'hasOpening', 'manualTypes'));
    }

    public function storeMovement(StoreCashMovementRequest $request, CashService $cashService): RedirectResponse
    {
        $cashService->addMovement($request->validated());

        return redirect()->route('cash.index')->with('success', 'Mouvement de caisse enregistré.');
    }
}
