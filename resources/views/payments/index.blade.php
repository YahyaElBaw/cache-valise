<x-app-layout>
    <x-slot name="title">Règlements</x-slot>
    <x-slot name="header">Règlements (Clients & Fournisseurs)</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau règlement
        </a>
    </x-slot>

    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label small text-muted mb-0">Sens</label>
                <select name="direction" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="in" @selected($direction === 'in')>Encaissement client (Entrée)</option>
                    <option value="out" @selected($direction === 'out')>Paiement charge (Sortie)</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label small text-muted mb-0">Mode</label>
                <select name="method" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($methods as $m)
                        <option value="{{ $m->value }}" @selected($method === $m->value)>{{ $m->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm">Filtrer</button>
                <a href="{{ route('payments.index') }}" class="btn btn-link btn-sm">Réinitialiser</a>
            </div>
        </form>
    </div></div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sens</th>
                        <th>Document</th>
                        <th>Mode</th>
                        <th>Référence</th>
                        <th class="text-end">Montant</th>
                        <th>Impact caisse</th>
                        <th>Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td>{{ $p->payment_date->format('d/m/Y') }}</td>
                            <td>
                                @if($p->direction === 'in')
                                    <span class="badge bg-success-subtle text-success">Client (+)</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Fournisseur (-)</span>
                                @endif
                            </td>
                            <td>
                                @if($p->sale)
                                    <a href="{{ route('sales.show', $p->sale) }}" class="text-decoration-none fw-semibold">{{ $p->sale->invoice_number }}</a>
                                    <span class="text-muted small">({{ $p->sale->customer?->name ?? 'Comptoir' }})</span>
                                @elseif($p->charge)
                                    <a href="{{ route('charges.show', $p->charge) }}" class="text-decoration-none fw-semibold">{{ $p->charge->number }}</a>
                                    <span class="text-muted small">({{ $p->charge->supplier?->name ?? $p->charge->label }})</span>
                                @endif
                            </td>
                            <td>{{ $p->method->label() }}</td>
                            <td>{{ $p->reference ?: '-' }}</td>
                            <td class="text-end fw-semibold"><x-money :value="$p->amount" /></td>
                            <td class="small">{{ $p->cashMovement ? 'Oui (#' . $p->cashMovement->id . ')' : 'Non (hors espèces)' }}</td>
                            <td><x-status-badge :status="$p->status" /></td>
                            <td class="text-end">
                                @if($p->status === 'valid')
                                    <x-confirm-cancel :action="route('payments.cancel', $p)" label="Annuler" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Aucun règlement enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="card-footer bg-white">{{ $payments->links() }}</div>
        @endif
    </div>
</x-app-layout>
