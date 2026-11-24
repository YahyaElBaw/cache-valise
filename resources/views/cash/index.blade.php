<x-app-layout>
    <x-slot name="title">Caisse espèces</x-slot>
    <x-slot name="header">Journal de caisse espèces</x-slot>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-start border-4 border-info h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Solde réel en caisse</div>
                    <div class="display-6 fw-bold text-info mt-2"><x-money :value="$currentBalance" /></div>
                    <p class="text-muted small mt-2 mb-0">
                        Seuls les règlements en <strong>espèces</strong> et les mouvements manuels affectent ce solde. Les virements et chèques sont suivis hors caisse.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Nouveau mouvement manuel</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cash.movements.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Type *</label>
                                <select name="type" id="cashMoveType" class="form-select" required>
                                    @foreach($manualTypes as $t)
                                        <option value="{{ $t->value }}" @selected(old('type') === $t->value)>{{ $t->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-none" id="correctionDirGroup">
                                <label class="form-label">Sens *</label>
                                <select name="direction" id="correctionDir" class="form-select">
                                    <option value="in">Entrée (+)</option>
                                    <option value="out">Sortie (-)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Montant (DT) *</label>
                                <input type="number" step="0.001" min="0.001" name="amount" value="{{ old('amount') }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date / Heure</label>
                                <input type="datetime-local" name="moved_at" value="{{ old('moved_at', now()->format('Y-m-d\TH:i')) }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Libellé / Motif <span id="motifReq" class="text-danger d-none">*</span></label>
                                <input type="text" name="label" value="{{ old('label') }}" class="form-control" placeholder="Ex : Apport personnel, Dépôt banque, Motif de correction...">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">Enregistrer le mouvement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Journal chronologique de la caisse</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date / Heure</th>
                        <th>Type</th>
                        <th>Libellé</th>
                        <th class="text-end">Entrée</th>
                        <th class="text-end">Sortie</th>
                        <th class="text-end">Solde après</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $m = $row['movement']; @endphp
                        <tr>
                            <td class="text-nowrap">{{ $m->moved_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $m->type->value === 'reversal' ? 'bg-warning text-dark' : 'bg-light text-dark border' }}">
                                    {{ $m->type->label() }}
                                </span>
                            </td>
                            <td>{{ $m->label }}</td>
                            <td class="text-end text-success fw-semibold">
                                {{ $m->direction === 'in' ? money($m->amount) : '' }}
                            </td>
                            <td class="text-end text-danger fw-semibold">
                                {{ $m->direction === 'out' ? money($m->amount) : '' }}
                            </td>
                            <td class="text-end fw-bold"><x-money :value="$row['running_balance']" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun mouvement de caisse.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const typeSel = document.getElementById('cashMoveType');
        const dirGroup = document.getElementById('correctionDirGroup');
        const motifReq = document.getElementById('motifReq');
        function toggle() {
            const isCorr = typeSel.value === 'correction';
            dirGroup.classList.toggle('d-none', !isCorr);
            motifReq.classList.toggle('d-none', !isCorr);
        }
        typeSel.addEventListener('change', toggle);
        toggle();
    });
    </script>
    @endpush
</x-app-layout>
