<x-app-layout>
    <x-slot name="title">Nouvelle charge</x-slot>
    <x-slot name="header">Enregistrer une charge de fabrication</x-slot>

    <div class="card" style="max-width: 820px;">
        <div class="card-body">
            <form method="POST" action="{{ route('charges.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Numéro de pièce *</label>
                        <input type="text" name="number" value="{{ old('number', $suggestedNumber) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de charge *</label>
                        <input type="date" name="charge_date" value="{{ old('charge_date', now()->toDateString()) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Catégorie *</label>
                        <select name="category" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}" @selected(old('category') === $cat->value)>{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lot concerné *</label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Choisir un lot --</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" @selected(old('batch_id', $selectedBatchId) == $b->id)>
                                    {{ $b->number }} ({{ $b->productModel->designation }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fournisseur / Prestataire</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- Aucun / Divers --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }} ({{ $s->type->label() }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Désignation / Libellé *</label>
                        <input type="text" name="label" value="{{ old('label') }}" class="form-control" placeholder="Ex : Achat tissu lycra, Façon couture..." required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantité *</label>
                        <input type="number" step="0.001" min="0.001" name="quantity" id="chg_qty" value="{{ old('quantity', '1.000') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unité *</label>
                        <input type="text" name="unit" value="{{ old('unit', 'forfait') }}" class="form-control" placeholder="m, pcs, forfait" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix unitaire (DT) *</label>
                        <input type="number" step="0.001" min="0" name="unit_price" id="chg_pu" value="{{ old('unit_price', '0.000') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Montant total</label>
                        <div class="form-control bg-light fw-bold text-primary" id="chg_total">0,000 DT</div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Enregistrer la charge</button>
                    <a href="{{ route('charges.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const qty = document.getElementById('chg_qty');
        const pu = document.getElementById('chg_pu');
        const out = document.getElementById('chg_total');
        function refresh() {
            const total = (parseFloat(qty.value || 0) * parseFloat(pu.value || 0));
            out.textContent = total.toLocaleString('fr-FR', { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + ' DT';
        }
        qty.addEventListener('input', refresh);
        pu.addEventListener('input', refresh);
        refresh();
    });
    </script>
    @endpush
</x-app-layout>
