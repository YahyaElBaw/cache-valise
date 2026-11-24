<x-app-layout>
    <x-slot name="title">Nouvelle facture</x-slot>
    <x-slot name="header">Nouvelle facture de vente</x-slot>

    <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
        @csrf
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">N° Facture *</label>
                        <input type="text" name="invoice_number" value="{{ old('invoice_number', $suggestedNumber) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de vente *</label>
                        <input type="date" name="sale_date" value="{{ old('sale_date', now()->toDateString()) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Client (optionnel = vente comptoir)</label>
                        <select name="customer_id" class="form-select">
                            <option value="">Vente comptoir (sans client)</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Lignes de facture</span>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addLineBtn">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                </button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="linesTable">
                    <thead>
                        <tr>
                            <th style="min-width: 260px;">Lot (modèle • reste • coût unit.)</th>
                            <th style="width: 120px;">Quantité</th>
                            <th style="width: 150px;">Prix unitaire (DT)</th>
                            <th style="width: 140px;">Remise (DT)</th>
                            <th class="text-end" style="width: 150px;">Total ligne</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-semibold">Total à payer :</td>
                            <td class="text-end fs-5 fw-bold text-primary" id="grandTotal">0,000 DT</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-body border-top d-none" id="qtyWarningBox">
                <div class="alert alert-warning mb-0 small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Certaines lignes dépassent la quantité restante indicative du lot. Conformément au cahier des charges, la vente reste autorisée.
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Valider la facture</button>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>

    @push('scripts')
    @php
        $batchOptions = $batches->map(fn ($b) => [
            'id' => $b->id,
            'label' => "{$b->number} — {$b->productModel->designation} (reste: {$b->remaining_qty}, coût: " . money($b->computed_unit_cost) . ")",
            'price' => (string) $b->productModel->suggested_price,
            'remaining' => (int) $b->remaining_qty,
        ])->values();
    @endphp
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const batches = @json($batchOptions);
        const tbody = document.getElementById('linesBody');
        const grandTotalEl = document.getElementById('grandTotal');
        const warnBox = document.getElementById('qtyWarningBox');
        let idx = 0;

        function fmt(n) {
            return n.toLocaleString('fr-FR', { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + ' DT';
        }

        function recalc() {
            let grand = 0;
            let hasWarn = false;
            tbody.querySelectorAll('tr').forEach(tr => {
                const sel = tr.querySelector('.line-batch');
                const qty = parseInt(tr.querySelector('.line-qty').value || 0, 10);
                const pu = parseFloat(tr.querySelector('.line-pu').value || 0);
                const disc = parseFloat(tr.querySelector('.line-disc').value || 0);
                const total = Math.max(0, (qty * pu) - disc);
                tr.querySelector('.line-total').textContent = fmt(total);
                grand += total;

                const opt = sel.options[sel.selectedIndex];
                if (opt && opt.dataset.remaining !== undefined && qty > parseInt(opt.dataset.remaining, 10)) {
                    hasWarn = true;
                }
            });
            grandTotalEl.textContent = fmt(grand);
            warnBox.classList.toggle('d-none', !hasWarn);
        }

        function addLine(preset = {}) {
            const tr = document.createElement('tr');
            const optionsHtml = batches.map(b =>
                `<option value="${b.id}" data-price="${b.price}" data-remaining="${b.remaining}" ${preset.batch_id == b.id ? 'selected' : ''}>${b.label}</option>`
            ).join('');

            const firstPrice = batches.length ? batches[0].price : '0.000';
            tr.innerHTML = `
                <td>
                    <select name="items[${idx}][batch_id]" class="form-select form-select-sm line-batch" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td><input type="number" min="1" name="items[${idx}][quantity]" value="${preset.quantity ?? 1}" class="form-control form-control-sm line-qty" required></td>
                <td><input type="number" step="0.001" min="0" name="items[${idx}][unit_price]" value="${preset.unit_price ?? firstPrice}" class="form-control form-control-sm line-pu" required></td>
                <td><input type="number" step="0.001" min="0" name="items[${idx}][discount]" value="${preset.discount ?? '0.000'}" class="form-control form-control-sm line-disc"></td>
                <td class="text-end fw-semibold line-total">0,000 DT</td>
                <td class="text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-line" title="Retirer"><i class="bi bi-trash"></i></button>
                </td>
            `;
            idx++;
            tbody.appendChild(tr);

            const sel = tr.querySelector('.line-batch');
            sel.addEventListener('change', () => {
                const opt = sel.options[sel.selectedIndex];
                if (opt && opt.dataset.price) {
                    tr.querySelector('.line-pu').value = opt.dataset.price;
                }
                recalc();
            });
            tr.querySelectorAll('input').forEach(inp => inp.addEventListener('input', recalc));
            tr.querySelector('.remove-line').addEventListener('click', () => {
                if (tbody.querySelectorAll('tr').length > 1) {
                    tr.remove();
                    recalc();
                }
            });
            recalc();
        }

        document.getElementById('addLineBtn').addEventListener('click', () => addLine());
        addLine();
    });
    </script>
    @endpush
</x-app-layout>
