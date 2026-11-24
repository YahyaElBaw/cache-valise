<x-app-layout>
    <x-slot name="title">Facture {{ $sale->invoice_number }}</x-slot>
    <x-slot name="header">Facture {{ $sale->invoice_number }}</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('sales.pdf', $sale) }}" target="_blank" class="btn btn-dark btn-sm">
            <i class="bi bi-file-pdf me-1"></i>Imprimer PDF
        </a>
        @if($sale->status === 'validated' && bccomp($amountDue, '0.000', 3) > 0)
            <a href="{{ route('payments.create', ['sale_id' => $sale->id]) }}" class="btn btn-success btn-sm">
                <i class="bi bi-cash me-1"></i>Enregistrer un règlement
            </a>
        @endif
        @if($sale->status === 'validated')
            <x-confirm-cancel :action="route('sales.cancel', $sale)" label="Annuler facture" message="Annuler la facture {{ $sale->invoice_number }} ? (Possible uniquement si aucun règlement valide n’est actif.)" />
        @endif
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body">
            <div class="text-muted small">Client</div>
            <div class="fw-bold">{{ $sale->customer?->name ?? 'Vente comptoir' }}</div>
            <div class="small text-muted">Date : {{ $sale->sale_date->format('d/m/Y') }}</div>
            <div class="mt-1"><x-status-badge :status="$sale->status" /></div>
        </div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body">
            <div class="text-muted small">Total facture</div>
            <div class="fs-4 fw-bold text-primary"><x-money :value="$sale->total" /></div>
            <div class="small text-muted">Remises : <x-money :value="$sale->discount_total" /></div>
        </div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body">
            <div class="text-muted small">Encaissé / Reste dû</div>
            <div class="fs-5 fw-bold text-success"><x-money :value="$amountPaid" /></div>
            <div class="small fw-semibold text-danger">Reste : <x-money :value="$amountDue" /></div>
        </div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body">
            <div class="text-muted small">Rentabilité (snapshot)</div>
            <div class="fs-5 fw-bold"><x-money :value="$grossMargin" /></div>
            <div class="small text-muted">COGS : <x-money :value="$cogs" /></div>
        </div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold">Lignes de la facture</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Lot / Modèle</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">PU Vente</th>
                        <th class="text-end">Remise</th>
                        <th class="text-end">Coût unit. (snapshot)</th>
                        <th class="text-end">Total ligne</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('batches.show', $item->batch) }}" class="fw-semibold text-decoration-none">{{ $item->batch->number }}</a>
                                — {{ $item->batch->productModel->designation }}
                            </td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end"><x-money :value="$item->unit_price" /></td>
                            <td class="text-end"><x-money :value="$item->discount" /></td>
                            <td class="text-end text-muted"><x-money :value="$item->unit_cost" /></td>
                            <td class="text-end fw-semibold"><x-money :value="$item->line_total" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Règlements</span>
            @if($sale->status === 'validated' && bccomp($amountDue, '0.000', 3) > 0)
                <a href="{{ route('payments.create', ['sale_id' => $sale->id]) }}" class="btn btn-sm btn-outline-success">+ Nouveau règlement</a>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Date</th><th>Mode</th><th>Référence</th><th class="text-end">Montant</th><th>Caisse</th><th>Statut</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse($sale->payments as $p)
                        <tr>
                            <td>{{ $p->payment_date->format('d/m/Y') }}</td>
                            <td>{{ $p->method->label() }}</td>
                            <td>{{ $p->reference ?: '-' }}</td>
                            <td class="text-end fw-semibold"><x-money :value="$p->amount" /></td>
                            <td class="small">{{ $p->cashMovement ? 'Entrée caisse #' . $p->cashMovement->id : 'Hors caisse' }}</td>
                            <td><x-status-badge :status="$p->status" /></td>
                            <td class="text-end">
                                @if($p->status === 'valid')
                                    <x-confirm-cancel :action="route('payments.cancel', $p)" label="Annuler" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Aucun règlement enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
