<x-app-layout>
    <x-slot name="title">{{ $supplier->name }}</x-slot>
    <x-slot name="header">Fiche fournisseur : {{ $supplier->name }}</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary btn-sm">Modifier</a>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Charges engagées</div>
            <div class="fs-4 fw-bold"><x-money :value="$totalCharged" /></div>
        </div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Total payé</div>
            <div class="fs-4 fw-bold text-success"><x-money :value="$totalPaid" /></div>
        </div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Dette fournisseur</div>
            <div class="fs-4 fw-bold text-danger"><x-money :value="$debt" /></div>
        </div></div></div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Historique des charges</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>N° Pièce</th><th>Date</th><th>Lot</th><th>Désignation</th><th class="text-end">Montant</th><th class="text-end">Reste à payer</th><th>Statut</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse ($supplier->charges as $c)
                        @php $due = $cashService->chargeDue($c); @endphp
                        <tr>
                            <td><a href="{{ route('charges.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->number }}</a></td>
                            <td>{{ $c->charge_date->format('d/m/Y') }}</td>
                            <td>{{ $c->batch->number }}</td>
                            <td>{{ $c->label }}</td>
                            <td class="text-end"><x-money :value="$c->amount" /></td>
                            <td class="text-end text-danger"><x-money :value="$due" /></td>
                            <td><x-status-badge :status="$c->status" /></td>
                            <td class="text-end">
                                @if($c->status === 'active' && bccomp($due, '0.000', 3) > 0)
                                    <a href="{{ route('payments.create', ['charge_id' => $c->id]) }}" class="btn btn-outline-success btn-sm">Payer</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">Aucune charge.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
