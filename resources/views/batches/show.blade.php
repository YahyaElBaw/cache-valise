<x-app-layout>
    <x-slot name="title">Lot {{ $batch->number }}</x-slot>
    <x-slot name="header">Détail du lot {{ $batch->number }}</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('charges.create', ['batch_id' => $batch->id]) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Ajouter une charge
        </a>
        <a href="{{ route('batches.edit', $batch) }}" class="btn btn-outline-secondary btn-sm">Modifier</a>
    </x-slot>

    <!-- Summary KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Modèle</div>
                <div class="fw-bold">{{ $batch->productModel->reference }} — {{ $batch->productModel->designation }}</div>
                <div class="mt-1"><x-status-badge :status="$batch->status" /></div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Coût total du lot</div>
                <div class="fs-4 fw-bold text-danger"><x-money :value="$totalCost" /></div>
                <div class="small text-muted">Somme des charges actives</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Coût de revient unitaire</div>
                <div class="fs-4 fw-bold text-primary"><x-money :value="$unitCost" /></div>
                <div class="small text-muted">Pour {{ $batch->quantity }} unités produites</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Quantités (indicatif)</div>
                <div class="fw-bold fs-5">{{ $soldQty }} vendues / {{ $remainingQty }} restantes</div>
                <div class="small text-muted">Sur {{ $batch->quantity }} produites</div>
            </div></div>
        </div>
    </div>

    <!-- Charges table -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Charges associées</span>
            <a href="{{ route('charges.create', ['batch_id' => $batch->id]) }}" class="btn btn-sm btn-outline-primary">+ Nouvelle charge</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Pièce</th>
                        <th>Date</th>
                        <th>Catégorie</th>
                        <th>Fournisseur</th>
                        <th>Désignation</th>
                        <th class="text-end">Qté × PU</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Payé / Dû</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batch->charges as $charge)
                        @php
                            $paid = $cashService->chargePaid($charge);
                            $due = $cashService->chargeDue($charge);
                        @endphp
                        <tr class="{{ $charge->status === 'cancelled' ? 'table-light text-muted' : '' }}">
                            <td><a href="{{ route('charges.show', $charge) }}" class="fw-semibold text-decoration-none">{{ $charge->number }}</a></td>
                            <td>{{ $charge->charge_date->format('d/m/Y') }}</td>
                            <td>{{ $charge->category->label() }}</td>
                            <td>{{ $charge->supplier?->name ?? '-' }}</td>
                            <td>{{ $charge->label }}</td>
                            <td class="text-end small">{{ number_format((float)$charge->quantity, 2, ',', ' ') }} {{ $charge->unit }} × <x-money :value="$charge->unit_price" /></td>
                            <td class="text-end fw-semibold"><x-money :value="$charge->amount" /></td>
                            <td class="text-end small">
                                <x-money :value="$paid" /> /
                                <span class="text-danger"><x-money :value="$due" /></span>
                            </td>
                            <td><x-status-badge :status="$charge->status" /></td>
                            <td class="text-end text-nowrap">
                                @if($charge->status === 'active' && bccomp($due, '0.000', 3) > 0)
                                    <a href="{{ route('payments.create', ['charge_id' => $charge->id]) }}" class="btn btn-outline-success btn-sm" title="Payer">
                                        <i class="bi bi-cash"></i>
                                    </a>
                                @endif
                                @if($charge->status === 'active')
                                    <a href="{{ route('charges.edit', $charge) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                    <x-confirm-cancel :action="route('charges.cancel', $charge)" label="" buttonClass="btn btn-outline-danger btn-sm" message="Annuler la charge {{ $charge->number }} ? Elle sortira du coût du lot." />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-3">Aucune charge pour ce lot.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sales of this lot -->
    <div class="card">
        <div class="card-header bg-white fw-semibold">Ventes issues de ce lot</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Facture</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th class="text-end">Qté</th>
                        <th class="text-end">PU Vente</th>
                        <th class="text-end">Coût unit. snapshot</th>
                        <th class="text-end">Total ligne</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batch->saleItems as $item)
                        <tr>
                            <td><a href="{{ route('sales.show', $item->sale) }}" class="fw-semibold text-decoration-none">{{ $item->sale->invoice_number }}</a></td>
                            <td>{{ $item->sale->sale_date->format('d/m/Y') }}</td>
                            <td>{{ $item->sale->customer?->name ?? 'Vente comptoir' }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end"><x-money :value="$item->unit_price" /></td>
                            <td class="text-end"><x-money :value="$item->unit_cost" /></td>
                            <td class="text-end fw-semibold"><x-money :value="$item->line_total" /></td>
                            <td><x-status-badge :status="$item->sale->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">Aucune vente sur ce lot.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
