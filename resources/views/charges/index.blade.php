<x-app-layout>
    <x-slot name="title">Charges de fabrication</x-slot>
    <x-slot name="header">Charges de fabrication</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('charges.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle charge
        </a>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label small text-muted mb-0">Lot</label>
                    <select name="batch_id" class="form-select form-select-sm">
                        <option value="">Tous les lots</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" @selected($batchId == $b->id)>{{ $b->number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-muted mb-0">Catégorie</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->value }}" @selected($category === $cat->value)>{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-muted mb-0">Du</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-sm-2">
                    <label class="form-label small text-muted mb-0">Au</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer</button>
                    <a href="{{ route('charges.index') }}" class="btn btn-link btn-sm">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Pièce</th>
                        <th>Date</th>
                        <th>Lot</th>
                        <th>Catégorie</th>
                        <th>Fournisseur</th>
                        <th>Désignation</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Payé</th>
                        <th class="text-end">Reste dû</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($charges as $charge)
                        @php
                            $paid = $cashService->chargePaid($charge);
                            $due = $cashService->chargeDue($charge);
                        @endphp
                        <tr>
                            <td><a href="{{ route('charges.show', $charge) }}" class="fw-semibold text-decoration-none">{{ $charge->number }}</a></td>
                            <td>{{ $charge->charge_date->format('d/m/Y') }}</td>
                            <td><a href="{{ route('batches.show', $charge->batch_id) }}" class="text-decoration-none">{{ $charge->batch->number }}</a></td>
                            <td>{{ $charge->category->label() }}</td>
                            <td>{{ $charge->supplier?->name ?? '-' }}</td>
                            <td>{{ $charge->label }}</td>
                            <td class="text-end fw-semibold"><x-money :value="$charge->amount" /></td>
                            <td class="text-end"><x-money :value="$paid" /></td>
                            <td class="text-end text-danger"><x-money :value="$due" /></td>
                            <td><x-status-badge :status="$charge->status" /></td>
                            <td class="text-end text-nowrap">
                                @if($charge->status === 'active' && bccomp($due, '0.000', 3) > 0)
                                    <a href="{{ route('payments.create', ['charge_id' => $charge->id]) }}" class="btn btn-outline-success btn-sm" title="Payer">
                                        <i class="bi bi-cash"></i>
                                    </a>
                                @endif
                                @if($charge->status === 'active')
                                    <a href="{{ route('charges.edit', $charge) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                    <x-confirm-cancel :action="route('charges.cancel', $charge)" label="" buttonClass="btn btn-outline-danger btn-sm" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted py-4">Aucune charge enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($charges->hasPages())
            <div class="card-footer bg-white">{{ $charges->links() }}</div>
        @endif
    </div>
</x-app-layout>
