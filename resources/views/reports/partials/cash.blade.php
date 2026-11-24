<div class="p-3 border-bottom bg-light d-flex flex-wrap gap-4">
    <div><span class="text-muted small">Entrées (période) :</span> <strong class="text-success">{{ money($data['totalIn']) }}</strong></div>
    <div><span class="text-muted small">Sorties (période) :</span> <strong class="text-danger">{{ money($data['totalOut']) }}</strong></div>
    <div><span class="text-muted small">Solde actuel :</span> <strong class="text-primary">{{ money($data['currentBalance']) }}</strong></div>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Date / Heure</th>
                <th>Type</th>
                <th>Libellé</th>
                <th class="text-end">Entrée</th>
                <th class="text-end">Sortie</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['movements'] as $m)
                <tr>
                    <td>{{ $m->moved_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->type->label() }}</td>
                    <td>{{ $m->label }}</td>
                    <td class="text-end text-success">{{ $m->direction === 'in' ? money($m->amount) : '' }}</td>
                    <td class="text-end text-danger">{{ $m->direction === 'out' ? money($m->amount) : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Aucun mouvement sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
