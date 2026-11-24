<div class="p-3 border-bottom bg-light d-flex flex-wrap gap-4">
    <div><span class="text-muted small">Total Ventes :</span> <strong>{{ money($data['totalRevenue']) }}</strong></div>
    <div><span class="text-muted small">Encaissé :</span> <strong class="text-success">{{ money($data['totalPaid']) }}</strong></div>
    <div><span class="text-muted small">Reste dû :</span> <strong class="text-danger">{{ money($data['totalDue']) }}</strong></div>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Facture</th>
                <th>Date</th>
                <th>Client</th>
                <th class="text-end">Total</th>
                <th class="text-end">Encaissé</th>
                <th class="text-end">Reste dû</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['sales'] as $s)
                <tr>
                    <td>{{ $s->invoice_number }}</td>
                    <td>{{ $s->sale_date->format('d/m/Y') }}</td>
                    <td>{{ $s->customer?->name ?? 'Vente comptoir' }}</td>
                    <td class="text-end fw-semibold">{{ money($s->total) }}</td>
                    <td class="text-end text-success">{{ money($s->paid_amount) }}</td>
                    <td class="text-end text-danger">{{ money($s->due_amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Aucune vente sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
