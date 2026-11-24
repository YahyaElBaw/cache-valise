<x-app-layout>
    <x-slot name="title">Clients</x-slot>
    <x-slot name="header">Clients</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau client
        </a>
    </x-slot>

    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-4"><input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Rechercher un client..."></div>
            <div class="col-auto"><button class="btn btn-outline-primary btn-sm">Rechercher</button></div>
        </form>
    </div></div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>E-mail</th>
                        <th class="text-end">Total facturé</th>
                        <th class="text-end">Solde dû</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $invoiced = '0.000';
                            $paid = '0.000';
                            foreach ($customer->sales as $s) {
                                $invoiced = bcadd($invoiced, (string) $s->total, 3);
                                $paid = bcadd($paid, $salesService->amountPaid($s), 3);
                            }
                            $due = bcsub($invoiced, $paid, 3);
                        @endphp
                        <tr>
                            <td><a href="{{ route('customers.show', $customer) }}" class="fw-semibold text-decoration-none">{{ $customer->name }}</a></td>
                            <td>{{ $customer->phone ?: '-' }}</td>
                            <td>{{ $customer->email ?: '-' }}</td>
                            <td class="text-end"><x-money :value="$invoiced" /></td>
                            <td class="text-end fw-semibold {{ bccomp($due, '0.000', 3) > 0 ? 'text-warning' : 'text-success' }}">
                                <x-money :value="$due" />
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun client enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-white">{{ $customers->links() }}</div>
        @endif
    </div>
</x-app-layout>
