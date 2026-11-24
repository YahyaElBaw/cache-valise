<x-app-layout>
    <x-slot name="title">Modifier lot</x-slot>
    <x-slot name="header">Modifier le lot {{ $batch->number }}</x-slot>

    <div class="card" style="max-width: 760px;">
        <div class="card-body">
            <form method="POST" action="{{ route('batches.update', $batch) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Numéro de lot *</label>
                        <input type="text" name="number" value="{{ old('number', $batch->number) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modèle *</label>
                        <select name="product_model_id" class="form-select" required>
                            @foreach($models as $m)
                                <option value="{{ $m->id }}" @selected(old('product_model_id', $batch->product_model_id) == $m->id)>
                                    {{ $m->reference }} — {{ $m->designation }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quantité produite *</label>
                        <input type="number" min="1" name="quantity" value="{{ old('quantity', $batch->quantity) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de production *</label>
                        <input type="date" name="production_date" value="{{ old('production_date', $batch->production_date->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut *</label>
                        <select name="status" class="form-select" required>
                            @foreach($statuses as $st)
                                <option value="{{ $st->value }}" @selected(old('status', $batch->status->value) === $st->value)>{{ $st->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de début</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $batch->start_date?->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $batch->end_date?->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observations</label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $batch->notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="{{ route('batches.show', $batch) }}" class="btn btn-outline-secondary">Retour</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
