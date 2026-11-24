<x-app-layout>
    <x-slot name="title">Modèles</x-slot>
    <x-slot name="header">Modèles de cache-valises</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('product-models.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau modèle
        </a>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-sm-5">
                    <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Rechercher par référence ou désignation...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Rechercher</button>
                    @if($search !== '')
                        <a href="{{ route('product-models.index') }}" class="btn btn-link btn-sm">Réinitialiser</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>Dimensions</th>
                        <th class="text-end">Prix conseillé</th>
                        <th class="text-center">Lots</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($models as $model)
                        <tr>
                            <td class="fw-semibold">{{ $model->reference }}</td>
                            <td>{{ $model->designation }}</td>
                            <td class="text-muted small">{{ $model->dimensions ?: '-' }}</td>
                            <td class="text-end"><x-money :value="$model->suggested_price" /></td>
                            <td class="text-center">{{ $model->batches_count }}</td>
                            <td><x-status-badge :status="$model->is_active ? '1' : '0'" /></td>
                            <td class="text-end text-nowrap">
                                <form method="POST" action="{{ route('product-models.toggle', $model) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Activer / Désactiver">
                                        <i class="bi bi-toggle-{{ $model->is_active ? 'on text-success' : 'off' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('product-models.edit', $model) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucun modèle enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($models->hasPages())
            <div class="card-footer bg-white">{{ $models->links() }}</div>
        @endif
    </div>
</x-app-layout>
