<x-app-layout>
    <x-slot name="title">Nouveau règlement</x-slot>
    <x-slot name="header">Enregistrer un règlement</x-slot>

    <div class="card" style="max-width: 720px;">
        <div class="card-body">
            <div class="alert alert-info small d-flex justify-content-between align-items-center">
                <span>Solde actuel de la caisse espèces :</span>
                <strong><x-money :value="$cashBalance" /></strong>
            </div>

            <form method="POST" action="{{ route('payments.store') }}">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">

                @if($sale)
                    <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="small text-muted">Facture client</div>
                        <div class="fw-bold">{{ $sale->invoice_number }} — {{ $sale->customer?->name ?? 'Vente comptoir' }}</div>
                        <div class="small">Reste à encaisser : <strong class="text-danger"><x-money :value="$amountDue" /></strong></div>
                    </div>
                @elseif($charge)
                    <input type="hidden" name="charge_id" value="{{ $charge->id }}">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="small text-muted">Charge fournisseur</div>
                        <div class="fw-bold">{{ $charge->number }} — {{ $charge->label }}</div>
                        <div class="small">Reste à payer : <strong class="text-danger"><x-money :value="$amountDue" /></strong></div>
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label">Type d’opération</label>
                        <select id="payTargetType" class="form-select">
                            <option value="sale">Encaissement facture client</option>
                            <option value="charge">Paiement charge fournisseur</option>
                        </select>
                    </div>
                    <div class="mb-3" id="saleSelectGroup">
                        <label class="form-label">Facture à encaisser *</label>
                        <select name="sale_id" id="saleSelect" class="form-select">
                            <option value="">-- Choisir une facture --</option>
                            @foreach($openSales as $s)
                                <option value="{{ $s->id }}">{{ $s->invoice_number }} ({{ $s->customer?->name ?? 'Comptoir' }}) — Total {{ money($s->total) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="chargeSelectGroup">
                        <label class="form-label">Charge à payer *</label>
                        <select name="charge_id" id="chargeSelect" class="form-select" disabled>
                            <option value="">-- Choisir une charge --</option>
                            @foreach($openCharges as $c)
                                <option value="{{ $c->id }}">{{ $c->number }} ({{ $c->supplier?->name ?? $c->label }}) — {{ money($c->amount) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date *</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Montant (DT) *</label>
                        <input type="number" step="0.001" min="0.001" name="amount" value="{{ old('amount', bccomp($amountDue, '0.000', 3) > 0 ? $amountDue : '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mode *</label>
                        <select name="method" class="form-select" required>
                            @foreach($methods as $m)
                                <option value="{{ $m->value }}" @selected(old('method', 'cash') === $m->value)>{{ $m->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Référence (N° chèque, virement, reçu...)</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="form-control">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Enregistrer le règlement</button>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    @if(!$sale && !$charge)
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const typeSel = document.getElementById('payTargetType');
        const saleGroup = document.getElementById('saleSelectGroup');
        const chargeGroup = document.getElementById('chargeSelectGroup');
        const saleSel = document.getElementById('saleSelect');
        const chargeSel = document.getElementById('chargeSelect');

        typeSel.addEventListener('change', () => {
            const isSale = typeSel.value === 'sale';
            saleGroup.classList.toggle('d-none', !isSale);
            chargeGroup.classList.toggle('d-none', isSale);
            saleSel.disabled = !isSale;
            chargeSel.disabled = isSale;
        });
    });
    </script>
    @endpush
    @endif
</x-app-layout>
