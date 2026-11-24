<x-app-layout>
    <x-slot name="title">{{ $customer->name }}</x-slot>
    <x-slot name="header">Fiche client : {{ $customer->name }}</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary btn-sm">Modifier</a>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Total facturé</div>
            <div class="fs-4 fw-bold"><x-money :value="$totalInvoiced" /></div>
        </div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Total encaissé</div>
            <div class="fs-4 fw-bold text-success"><x-money :value="$totalPaid" /></div>
        </div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body">
            <div class="text-muted small">Solde dû</div>
            <div class="fs-4 fw-bold text-warning"><x-money :value="$amountDue" /></div>
        </div></div></div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Historique des factures</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Facture</th><th>Date</th><th class="text-end">Total</th><th class="text-end">Encaissé</th><th class="text-end">Reste dû</th><th>Statut</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse ($customer->sales as $sale)
                        @php
                            $paid = $salesService->amountPaid($sale);
                            $due = $salesService->amountDue($sale);
                        @endphp
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}" class="fw-semibold text-decoration-none">{{ $sale->invoice_number }}</a></td>
                            <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td class="text-end"><x-money :value="$sale->total" /></td>
                            <td class="text-end"><x-money :value="$paid" /></td>
                            <td class="text-end text-danger"><x-money :value="$due" /></td>
                            <td><x-status-badge :status="$sale->status" /></td>
                            <td class="text-end">
                                @if($sale->status === 'validated' && bccomp($due, '0.000', 3) > 0)
                                    <a href="{{ route('payments.create', ['sale_id' => $sale->id]) }}" class="btn btn-outline-success btn-sm">Encaisser</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Aucune facture.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
