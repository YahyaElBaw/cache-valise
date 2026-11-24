<x-app-layout>
    <x-slot name="title">Charge {{ $charge->number }}</x-slot>
    <x-slot name="header">Détail de la charge {{ $charge->number }}</x-slot>
    @php
        $paid = $cashService->chargePaid($charge);
        $due = $cashService->chargeDue($charge);
    @endphp
    <x-slot name="headerActions">
        @if($charge->status === 'active' && bccomp($due, '0.000', 3) > 0)
            <a href="{{ route('payments.create', ['charge_id' => $charge->id]) }}" class="btn btn-success btn-sm">
                <i class="bi bi-cash me-1"></i>Payer cette charge
            </a>
        @endif
        <a href="{{ route('batches.show', $charge->batch_id) }}" class="btn btn-outline-secondary btn-sm">Voir le lot</a>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Montant total</div>
            <div class="fs-4 fw-bold"><x-money :value="$charge->amount" /></div>
            <div class="small text-muted">{{ $charge->label }} ({{ $charge->category->label() }})</div>
        </div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Déjà payé</div>
            <div class="fs-4 fw-bold text-success"><x-money :value="$paid" /></div>
            <div class="small text-muted">Fournisseur : {{ $charge->supplier?->name ?? 'Non précisé' }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Reste à payer</div>
            <div class="fs-4 fw-bold text-danger"><x-money :value="$due" /></div>
            <div><x-status-badge :status="$charge->status" /></div>
        </div></div></div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Historique des paiements</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Mode</th>
                        <th>Référence</th>
                        <th class="text-end">Montant</th>
                        <th>Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($charge->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td>{{ $payment->method->label() }}</td>
                            <td>{{ $payment->reference ?: '-' }}</td>
                            <td class="text-end fw-semibold"><x-money :value="$payment->amount" /></td>
                            <td><x-status-badge :status="$payment->status" /></td>
                            <td class="text-end">
                                @if($payment->status === 'valid')
                                    <x-confirm-cancel :action="route('payments.cancel', $payment)" label="Annuler" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Aucun paiement enregistré pour cette charge.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
