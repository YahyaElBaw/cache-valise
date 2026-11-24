<div class="p-3 border-bottom bg-light d-flex flex-wrap gap-4">
    @foreach($data['byCategory'] as $catLabel => $catTotal)
        <div><span class="text-muted small">{{ $catLabel }} :</span> <strong>{{ money($catTotal) }}</strong></div>
    @endforeach
    <div class="ms-auto"><span class="text-muted small">Total charges :</span> <strong class="text-primary">{{ money($data['total']) }}</strong></div>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>N° Pièce</th>
                <th>Date</th>
                <th>Lot</th>
                <th>Catégorie</th>
                <th>Fournisseur</th>
                <th>Désignation</th>
                <th class="text-end">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['charges'] as $c)
                <tr>
                    <td>{{ $c->number }}</td>
                    <td>{{ $c->charge_date->format('d/m/Y') }}</td>
                    <td>{{ $c->batch?->number }}</td>
                    <td>{{ $c->category->label() }}</td>
                    <td>{{ $c->supplier?->name ?? '-' }}</td>
                    <td>{{ $c->label }}</td>
                    <td class="text-end fw-semibold">{{ money($c->amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Aucune charge sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
