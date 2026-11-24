<?php

namespace App\Services;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    /** @var array<int, string> */
    public array $lastWarnings = [];

    public function __construct(
        protected ProductionCostService $costService,
    ) {}

    public function createSale(array $data): Sale
    {
        $this->lastWarnings = [];

        return DB::transaction(function () use ($data) {
            $invoiceNumber = ! empty($data['invoice_number'])
                ? trim((string) $data['invoice_number'])
                : $this->generateInvoiceNumber();

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'sale_date' => $data['sale_date'],
                'customer_id' => $data['customer_id'] ?? null,
                'subtotal' => '0.000',
                'discount_total' => '0.000',
                'total' => '0.000',
                'status' => 'validated',
            ]);

            $subtotal = '0.000';
            $discountTotal = '0.000';
            $total = '0.000';

            foreach ($data['items'] as $itemData) {
                /** @var Batch $batch */
                $batch = Batch::query()->lockForUpdate()->findOrFail($itemData['batch_id']);

                if ($batch->status === BatchStatus::CANCELLED) {
                    throw ValidationException::withMessages([
                        'items' => "Le lot {$batch->number} est annulé et ne peut pas être vendu.",
                    ]);
                }

                $qty = (int) $itemData['quantity'];
                $unitPrice = bcadd((string) $itemData['unit_price'], '0', 3);
                $discount = bcadd((string) ($itemData['discount'] ?? '0'), '0', 3);

                $gross = bcmul((string) $qty, $unitPrice, 3);
                if (bccomp($discount, $gross, 3) > 0) {
                    throw ValidationException::withMessages([
                        'items' => "La remise sur le lot {$batch->number} ne peut pas dépasser le montant de la ligne.",
                    ]);
                }

                $remainingBefore = $this->costService->remainingQuantity($batch);
                if ($qty > $remainingBefore) {
                    $this->lastWarnings[] = "Attention : la quantité vendue ({$qty}) dépasse la quantité restante indicative ({$remainingBefore}) du lot {$batch->number}. La vente a tout de même été enregistrée.";
                }

                $unitCostSnapshot = $this->costService->unitCost($batch);
                $lineTotal = bcsub($gross, $discount, 3);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'batch_id' => $batch->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'unit_cost' => $unitCostSnapshot,
                    'line_total' => $lineTotal,
                ]);

                $subtotal = bcadd($subtotal, $gross, 3);
                $discountTotal = bcadd($discountTotal, $discount, 3);
                $total = bcadd($total, $lineTotal, 3);
            }

            $sale->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'total' => $total,
            ]);

            ActivityLogger::log(
                'create',
                Sale::class,
                $sale->id,
                "Création de la facture {$sale->invoice_number} pour un total de " . money($total) . '.'
            );

            return $sale->fresh(['customer', 'items.batch.productModel', 'payments']);
        });
    }

    public function amountPaid(Sale $sale): string
    {
        $payments = $sale->payments()
            ->where('status', 'valid')
            ->where('direction', 'in')
            ->get(['amount']);

        $paid = '0.000';
        foreach ($payments as $payment) {
            $paid = bcadd($paid, (string) $payment->amount, 3);
        }

        return $paid;
    }

    public function amountDue(Sale $sale): string
    {
        if ($sale->status === 'cancelled') {
            return '0.000';
        }

        return bcsub((string) $sale->total, $this->amountPaid($sale), 3);
    }

    public function cogs(Sale $sale): string
    {
        $total = '0.000';
        foreach ($sale->items as $item) {
            $total = bcadd($total, bcmul((string) $item->quantity, (string) $item->unit_cost, 3), 3);
        }

        return $total;
    }

    public function grossMargin(Sale $sale): string
    {
        return bcsub((string) $sale->total, $this->cogs($sale), 3);
    }

    public function cancelSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::query()->lockForUpdate()->findOrFail($sale->id);

            if ($lockedSale->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'sale' => 'Cette facture est déjà annulée.',
                ]);
            }

            if ($lockedSale->validPayments()->exists()) {
                throw ValidationException::withMessages([
                    'sale' => "Impossible d'annuler cette facture tant qu'elle possède des règlements valides. Annulez d'abord les règlements.",
                ]);
            }

            $lockedSale->update(['status' => 'cancelled']);

            ActivityLogger::log(
                'cancel',
                Sale::class,
                $lockedSale->id,
                "Annulation de la facture {$lockedSale->invoice_number}."
            );
        });
    }

    protected function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "FAC-{$year}-";

        $last = Sale::query()
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = 1;
        if ($last && preg_match('/(\d+)$/', $last, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Sale::query()->where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
