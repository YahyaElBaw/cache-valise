<x-app-layout>
    <x-slot name="title">Modifier modèle</x-slot>
    <x-slot name="header">Modifier {{ $productModel->reference }}</x-slot>

    <div class="card" style="max-width: 720px;">
        <div class="card-body">
            <form method="POST" action="{{ route('product-models.update', $productModel) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Référence *</label>
                        <input type="text" name="reference" value="{{ old('reference', $productModel->reference) }}" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Désignation *</label>
                        <input type="text" name="designation" value="{{ old('designation', $productModel->designation) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dimensions</label>
                        <input type="text" name="dimensions" value="{{ old('dimensions', $productModel->dimensions) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prix conseillé (DT) *</label>
                        <input type="number" step="0.001" min="0" name="suggested_price" value="{{ old('suggested_price', $productModel->suggested_price) }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $productModel->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $productModel->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Modèle actif</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="{{ route('product-models.index') }}" class="btn btn-outline-secondary">Retour</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
