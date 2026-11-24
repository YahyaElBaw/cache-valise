<x-app-layout>
    <x-slot name="title">Ventes & Factures</x-slot>
    <x-slot name="header">Ventes & Factures</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle facture
        </a>
    </x-slot>

    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label small text-muted mb-0">Client</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">Tous les clients</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected($customerId == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label small text-muted mb-0">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="validated" @selected($status === 'validated')>Validée</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Annulée</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm">Filtrer</button>
                <a href="{{ route('sales.index') }}" class="btn btn-link btn-sm">Réinitialiser</a>
            </div>
        </form>
    </div></div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Encaissé</th>
                        <th class="text-end">Reste dû</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        @php
                            $paid = $salesService->amountPaid($sale);
                            $due = $salesService->amountDue($sale);
                        @endphp
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}" class="fw-semibold text-decoration-none">{{ $sale->invoice_number }}</a></td>
                            <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td>{{ $sale->customer?->name ?? 'Vente comptoir' }}</td>
                            <td class="text-end fw-semibold"><x-money :value="$sale->total" /></td>
                            <td class="text-end text-success"><x-money :value="$paid" /></td>
                            <td class="text-end {{ bccomp($due, '0.000', 3) > 0 ? 'text-danger fw-semibold' : '' }}"><x-money :value="$due" /></td>
                            <td><x-status-badge :status="$sale->status" /></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('sales.pdf', $sale) }}" target="_blank" class="btn btn-outline-dark btn-sm" title="PDF"><i class="bi bi-file-pdf"></i></a>
                                @if($sale->status === 'validated' && bccomp($due, '0.000', 3) > 0)
                                    <a href="{{ route('payments.create', ['sale_id' => $sale->id]) }}" class="btn btn-outline-success btn-sm" title="Encaisser"><i class="bi bi-cash"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Aucune facture enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
            <div class="card-footer bg-white">{{ $sales->links() }}</div>
        @endif
    </div>
</x-app-layout>
