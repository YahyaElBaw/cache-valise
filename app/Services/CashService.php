<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\PaymentMethod;
use App\Models\CashMovement;
use App\Models\Charge;
use App\Models\Payment;
use App\Models\Sale;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashService
{
    public function balance(?string $until = null): string
    {
        $query = CashMovement::query();
        if ($until !== null) {
            $query->where('moved_at', '<=', $until);
        }

        $movements = $query->get(['direction', 'amount']);

        $in = '0.000';
        $out = '0.000';
        foreach ($movements as $movement) {
            if ($movement->direction === 'in') {
                $in = bcadd($in, (string) $movement->amount, 3);
            } else {
                $out = bcadd($out, (string) $movement->amount, 3);
            }
        }

        return bcsub($in, $out, 3);
    }

    public function chargePaid(Charge $charge): string
    {
        $payments = $charge->payments()
            ->where('status', 'valid')
            ->where('direction', 'out')
            ->get(['amount']);

        $paid = '0.000';
        foreach ($payments as $payment) {
            $paid = bcadd($paid, (string) $payment->amount, 3);
        }

        return $paid;
    }

    public function chargeDue(Charge $charge): string
    {
        if ($charge->status === 'cancelled') {
            return '0.000';
        }

        return bcsub((string) $charge->amount, $this->chargePaid($charge), 3);
    }

    public function recordPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['idempotency_key'])) {
                $existing = Payment::query()
                    ->where('idempotency_key', $data['idempotency_key'])
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $saleId = $data['sale_id'] ?? null;
            $chargeId = $data['charge_id'] ?? null;

            if (($saleId && $chargeId) || (! $saleId && ! $chargeId)) {
                throw ValidationException::withMessages([
                    'amount' => 'Un règlement doit concerner soit une facture, soit une charge.',
                ]);
            }

            $amount = bcadd((string) $data['amount'], '0', 3);
            if (bccomp($amount, '0.000', 3) <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Le montant du règlement doit être strictement positif.',
                ]);
            }

            $method = $data['method'] instanceof PaymentMethod
                ? $data['method']
                : PaymentMethod::from((string) $data['method']);

            $direction = $saleId ? 'in' : 'out';

            if ($saleId) {
                /** @var Sale $sale */
                $sale = Sale::query()->lockForUpdate()->findOrFail($saleId);
                if ($sale->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'amount' => 'Impossible de régler une facture annulée.',
                    ]);
                }

                $paid = '0.000';
                foreach ($sale->payments()->where('status', 'valid')->where('direction', 'in')->get(['amount']) as $p) {
                    $paid = bcadd($paid, (string) $p->amount, 3);
                }
                $due = bcsub((string) $sale->total, $paid, 3);

                if (bccomp($amount, $due, 3) > 0) {
                    throw ValidationException::withMessages([
                        'amount' => 'Le montant saisi (' . money($amount) . ') dépasse le reste à payer (' . money($due) . ').',
                    ]);
                }

                $targetLabel = "Règlement facture {$sale->invoice_number}";
            } else {
                /** @var Charge $charge */
                $charge = Charge::query()->lockForUpdate()->findOrFail($chargeId);
                if ($charge->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'amount' => 'Impossible de régler une charge annulée.',
                    ]);
                }

                $due = $this->chargeDue($charge);
                if (bccomp($amount, $due, 3) > 0) {
                    throw ValidationException::withMessages([
                        'amount' => 'Le montant saisi (' . money($amount) . ') dépasse le reste à payer (' . money($due) . ').',
                    ]);
                }

                if ($method === PaymentMethod::CASH && bccomp($amount, $this->balance(), 3) > 0) {
                    throw ValidationException::withMessages([
                        'amount' => 'Solde de caisse insuffisant (' . money($this->balance()) . ' disponible) pour cette sortie en espèces.',
                    ]);
                }

                $targetLabel = "Paiement charge {$charge->number}";
            }

            $payment = Payment::create([
                'payment_date' => $data['payment_date'],
                'direction' => $direction,
                'sale_id' => $saleId,
                'charge_id' => $chargeId,
                'amount' => $amount,
                'method' => $method,
                'reference' => $data['reference'] ?? null,
                'status' => 'valid',
                'idempotency_key' => $data['idempotency_key'] ?? null,
            ]);

            if ($method === PaymentMethod::CASH) {
                $this->createCashMovementForPayment($payment, $targetLabel);
            }

            ActivityLogger::log(
                'create',
                Payment::class,
                $payment->id,
                "{$targetLabel} de " . money($amount) . " ({$method->label()})."
            );

            return $payment->fresh(['sale', 'charge', 'cashMovement']);
        });
    }

    public function createCashMovementForPayment(Payment $payment, ?string $label = null): ?CashMovement
    {
        if ($payment->method !== PaymentMethod::CASH) {
            return null;
        }

        $type = $payment->direction === 'in'
            ? CashMovementType::CUSTOMER_PAYMENT
            : CashMovementType::CHARGE_PAYMENT;

        $computedLabel = $label ?? ($payment->sale_id
            ? "Règlement facture {$payment->sale?->invoice_number}"
            : "Paiement charge {$payment->charge?->number}");

        return CashMovement::firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'moved_at' => $payment->payment_date->format('Y-m-d') . ' ' . now()->format('H:i:s'),
                'type' => $type,
                'direction' => $payment->direction,
                'amount' => $payment->amount,
                'label' => $computedLabel,
            ]
        );
    }

    public function cancelPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'payment' => 'Ce règlement est déjà annulé.',
                ]);
            }

            $movement = $lockedPayment->cashMovement()->lockForUpdate()->first();
            if ($movement && ! $movement->reversedBy()->exists()) {
                $reversalDirection = $movement->direction === 'in' ? 'out' : 'in';

                if ($reversalDirection === 'out' && bccomp((string) $movement->amount, $this->balance(), 3) > 0) {
                    throw ValidationException::withMessages([
                        'payment' => 'Impossible d’annuler cet encaissement : le solde actuel de la caisse (' . money($this->balance()) . ') deviendrait négatif.',
                    ]);
                }

                CashMovement::create([
                    'moved_at' => now(),
                    'type' => CashMovementType::REVERSAL,
                    'direction' => $reversalDirection,
                    'amount' => $movement->amount,
                    'label' => 'Annulation : ' . $movement->label,
                    'payment_id' => null,
                    'reverses_id' => $movement->id,
                ]);
            }

            $lockedPayment->update(['status' => 'cancelled']);

            ActivityLogger::log(
                'cancel',
                Payment::class,
                $lockedPayment->id,
                "Annulation du règlement #{$lockedPayment->id} de " . money($lockedPayment->amount) . '.'
            );
        });
    }

    public function addMovement(array $data): CashMovement
    {
        return DB::transaction(function () use ($data) {
            $type = $data['type'] instanceof CashMovementType
                ? $data['type']
                : CashMovementType::from((string) $data['type']);

            $amount = bcadd((string) $data['amount'], '0', 3);
            if (bccomp($amount, '0.000', 3) <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Le montant doit être strictement positif.',
                ]);
            }

            if ($type === CashMovementType::OPENING && CashMovement::query()->where('type', CashMovementType::OPENING)->exists()) {
                throw ValidationException::withMessages([
                    'type' => 'Le solde initial a déjà été enregistré.',
                ]);
            }

            $direction = match ($type) {
                CashMovementType::OPENING, CashMovementType::TOP_UP => 'in',
                CashMovementType::WITHDRAWAL, CashMovementType::BANK_DEPOSIT => 'out',
                CashMovementType::CORRECTION => in_array(($data['direction'] ?? ''), ['in', 'out'], true)
                    ? $data['direction']
                    : throw ValidationException::withMessages(['direction' => 'Le sens (entrée/sortie) est obligatoire pour une correction.']),
                default => throw ValidationException::withMessages(['type' => 'Type de mouvement manuel invalide.']),
            };

            $label = trim((string) ($data['label'] ?? ''));
            if ($type === CashMovementType::CORRECTION && $label === '') {
                throw ValidationException::withMessages([
                    'label' => 'Un motif est obligatoire pour une correction de caisse.',
                ]);
            }

            if ($label === '') {
                $label = $type->label();
            }

            if ($direction === 'out' && bccomp($amount, $this->balance(), 3) > 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Solde de caisse insuffisant (' . money($this->balance()) . ' disponible).',
                ]);
            }

            $movement = CashMovement::create([
                'moved_at' => $data['moved_at'] ?? now(),
                'type' => $type,
                'direction' => $direction,
                'amount' => $amount,
                'label' => $label,
            ]);

            ActivityLogger::log(
                'create',
                CashMovement::class,
                $movement->id,
                "Mouvement de caisse ({$type->label()}, {$direction}) : " . money($amount) . " — {$label}"
            );

            return $movement;
        });
    }
}
