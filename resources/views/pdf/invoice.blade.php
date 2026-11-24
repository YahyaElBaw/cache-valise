<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        h1 { margin: 0; font-size: 20px; color: #0f172a; }
        .meta { margin-bottom: 18px; width: 100%; }
        .meta td { vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th, table.items td { border: 1px solid #cbd5e1; padding: 7px 8px; }
        table.items th { background: #f1f5f9; text-align: left; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .totals { width: 45%; margin-left: auto; margin-top: 16px; border-collapse: collapse; }
        .totals td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .totals tr.grand td { font-weight: bold; font-size: 14px; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a; }
        .badge-cancel { color: #dc2626; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <table class="meta">
        <tr>
            <td>
                <h1>FACTURE {{ $sale->invoice_number }}</h1>
                <div>Date : {{ $sale->sale_date->format('d/m/Y') }}</div>
                @if($sale->status === 'cancelled')
                    <div class="badge-cancel">FACTURE ANNULÉE</div>
                @endif
            </td>
            <td class="text-right">
                <strong>Atelier Cache-Valises</strong><br>
                Gestion de Production & Commercialisation<br>
                Devise : Dinar Tunisien (DT)
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td>
                <strong>Client :</strong><br>
                {{ $sale->customer?->name ?? 'Vente comptoir' }}<br>
                @if($sale->customer?->phone) Tél : {{ $sale->customer->phone }}<br> @endif
                @if($sale->customer?->address) {{ $sale->customer->address }} @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Lot</th>
                <th>Produit</th>
                <th class="text-right">Qté</th>
                <th class="text-right">Prix Unitaire</th>
                <th class="text-right">Remise</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->batch->number }}</td>
                    <td>{{ $item->batch->productModel->reference }} — {{ $item->batch->productModel->designation }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ money($item->unit_price) }}</td>
                    <td class="text-right">{{ money($item->discount) }}</td>
                    <td class="text-right">{{ money($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Sous-total</td><td class="text-right">{{ money($sale->subtotal) }}</td></tr>
        <tr><td>Remises</td><td class="text-right">{{ money($sale->discount_total) }}</td></tr>
        <tr class="grand"><td>Total à payer</td><td class="text-right">{{ money($sale->total) }}</td></tr>
        <tr><td>Montant encaissé</td><td class="text-right">{{ money($amountPaid) }}</td></tr>
        <tr><td><strong>Reste à payer</strong></td><td class="text-right"><strong>{{ money($amountDue) }}</strong></td></tr>
    </table>
</body>
</html>
