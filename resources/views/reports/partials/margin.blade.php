<div class="p-3 border-bottom bg-light d-flex flex-wrap gap-4">
    <div><span class="text-muted small">Chiffre d’affaires (CA) :</span> <strong>{{ money($data['totalCa']) }}</strong></div>
    <div><span class="text-muted small">Coût des produits vendus (COGS) :</span> <strong>{{ money($data['totalCogs']) }}</strong></div>
    <div><span class="text-muted small">Marge brute :</span> <strong class="text-success">{{ money($data['totalMargin']) }}</strong></div>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Facture</th>
                <th>Date</th>
                <th>Client</th>
                <th class="text-end">CA</th>
                <th class="text-end">COGS (snapshot)</th>
                <th class="text-end">Marge brute</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['sales'] as $s)
                <tr>
                    <td>{{ $s->invoice_number }}</td>
                    <td>{{ $s->sale_date->format('d/m/Y') }}</td>
                    <td>{{ $s->customer?->name ?? 'Vente comptoir' }}</td>
                    <td class="text-end">{{ money($s->total) }}</td>
                    <td class="text-end">{{ money($s->cogs_amount) }}</td>
                    <td class="text-end fw-semibold text-success">{{ money($s->margin_amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Aucune vente sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
