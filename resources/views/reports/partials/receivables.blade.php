<div class="p-3 border-bottom bg-light">
    <span class="text-muted small">Total créances clients :</span> <strong class="text-danger">{{ money($data['totalDue']) }}</strong>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Client</th>
                <th>Téléphone</th>
                <th class="text-end">Total facturé</th>
                <th class="text-end">Total encaissé</th>
                <th class="text-end">Créance restante</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $r)
                <tr>
                    <td class="fw-semibold">{{ $r['name'] }}</td>
                    <td>{{ $r['phone'] ?? '-' }}</td>
                    <td class="text-end">{{ money($r['invoiced']) }}</td>
                    <td class="text-end text-success">{{ money($r['paid']) }}</td>
                    <td class="text-end fw-semibold text-danger">{{ money($r['due']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Aucune créance.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
