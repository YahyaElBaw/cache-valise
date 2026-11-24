<x-app-layout>
    <x-slot name="title">Nouveau lot</x-slot>
    <x-slot name="header">Créer un lot de production</x-slot>

    <div class="card" style="max-width: 760px;">
        <div class="card-body">
            <form method="POST" action="{{ route('batches.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Numéro de lot *</label>
                        <input type="text" name="number" value="{{ old('number', 'LOT-' . now()->format('Y') . '-001') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modèle *</label>
                        <select name="product_model_id" class="form-select" required>
                            <option value="">-- Choisir un modèle --</option>
                            @foreach($models as $m)
                                <option value="{{ $m->id }}" @selected(old('product_model_id') == $m->id)>
                                    {{ $m->reference }} — {{ $m->designation }} ({{ money($m->suggested_price) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quantité produite *</label>
                        <input type="number" min="1" name="quantity" value="{{ old('quantity', 100) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de production *</label>
                        <input type="date" name="production_date" value="{{ old('production_date', now()->toDateString()) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut *</label>
                        <select name="status" class="form-select" required>
                            @foreach($statuses as $st)
                                <option value="{{ $st->value }}" @selected(old('status', 'preparing') === $st->value)>{{ $st->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de début</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observations</label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Enregistrer le lot</button>
                    <a href="{{ route('batches.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
