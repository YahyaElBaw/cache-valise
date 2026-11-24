<div class="p-3 border-bottom bg-light">
    <span class="text-muted small">Total dettes fournisseurs :</span> <strong class="text-danger">{{ money($data['totalDebt']) }}</strong>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Fournisseur</th>
                <th>Type</th>
                <th class="text-end">Charges engagées</th>
                <th class="text-end">Total payé</th>
                <th class="text-end">Dette restante</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $r)
                <tr>
                    <td class="fw-semibold">{{ $r['name'] }}</td>
                    <td>{{ $r['type'] }}</td>
                    <td class="text-end">{{ money($r['charged']) }}</td>
                    <td class="text-end text-success">{{ money($r['paid']) }}</td>
                    <td class="text-end fw-semibold text-danger">{{ money($r['debt']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Aucune dette fournisseur.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
