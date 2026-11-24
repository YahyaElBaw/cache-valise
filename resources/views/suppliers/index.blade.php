<x-app-layout>
    <x-slot name="title">Fournisseurs</x-slot>
    <x-slot name="header">Fournisseurs & Prestataires</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau fournisseur
        </a>
    </x-slot>

    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-4"><input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Rechercher un fournisseur..."></div>
            <div class="col-auto"><button class="btn btn-outline-primary btn-sm">Rechercher</button></div>
        </form>
    </div></div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Téléphone</th>
                        <th class="text-end">Total charges</th>
                        <th class="text-end">Dette restante</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        @php
                            $charged = '0.000';
                            $paid = '0.000';
                            foreach ($supplier->charges as $c) {
                                $charged = bcadd($charged, (string) $c->amount, 3);
                                $paid = bcadd($paid, $cashService->chargePaid($c), 3);
                            }
                            $debt = bcsub($charged, $paid, 3);
                        @endphp
                        <tr>
                            <td><a href="{{ route('suppliers.show', $supplier) }}" class="fw-semibold text-decoration-none">{{ $supplier->name }}</a></td>
                            <td>{{ $supplier->type->label() }}</td>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                            <td class="text-end"><x-money :value="$charged" /></td>
                            <td class="text-end fw-semibold {{ bccomp($debt, '0.000', 3) > 0 ? 'text-danger' : 'text-success' }}">
                                <x-money :value="$debt" />
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun fournisseur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="card-footer bg-white">{{ $suppliers->links() }}</div>
        @endif
    </div>
</x-app-layout>
