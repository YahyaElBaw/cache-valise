<x-app-layout>
    <x-slot name="title">Lots de production</x-slot>
    <x-slot name="header">Lots de production</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('batches.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau lot
        </a>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-sm-4">
                    <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="N° de lot (ex: LOT-2026-001)">
                </div>
                <div class="col-sm-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->value }}" @selected($status === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer</button>
                    <a href="{{ route('batches.index') }}" class="btn btn-link btn-sm">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Lot</th>
                        <th>Modèle</th>
                        <th>Date</th>
                        <th class="text-end">Produite</th>
                        <th class="text-end">Vendue / Reste</th>
                        <th class="text-end">Coût total</th>
                        <th class="text-end">Coût unitaire</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $sold = $costService->soldQuantity($batch);
                            $rem = $costService->remainingQuantity($batch);
                        @endphp
                        <tr>
                            <td><a href="{{ route('batches.show', $batch) }}" class="fw-semibold text-decoration-none">{{ $batch->number }}</a></td>
                            <td>{{ $batch->productModel->reference }} — {{ $batch->productModel->designation }}</td>
                            <td>{{ $batch->production_date->format('d/m/Y') }}</td>
                            <td class="text-end">{{ $batch->quantity }}</td>
                            <td class="text-end">
                                {{ $sold }} /
                                <span class="{{ $rem < 0 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $rem }}</span>
                            </td>
                            <td class="text-end"><x-money :value="$costService->totalCost($batch)" /></td>
                            <td class="text-end fw-semibold"><x-money :value="$costService->unitCost($batch)" /></td>
                            <td><x-status-badge :status="$batch->status" /></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('batches.show', $batch) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('batches.edit', $batch) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Aucun lot enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
            <div class="card-footer bg-white">{{ $batches->links() }}</div>
        @endif
    </div>
</x-app-layout>
