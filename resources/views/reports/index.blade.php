<x-app-layout>
    <x-slot name="title">Rapports</x-slot>
    <x-slot name="header">Rapports & Exports</x-slot>

    @php
        $tabs = [
            'charges' => 'Charges',
            'sales' => 'Ventes',
            'margin' => 'Marge brute',
            'receivables' => 'Créances clients',
            'debts' => 'Dettes fournisseurs',
            'cash' => 'Journal de caisse',
        ];
    @endphp

    <ul class="nav nav-pills mb-3 gap-1">
        @foreach($tabs as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $type === $key ? 'active' : 'bg-white border text-dark' }}"
                   href="{{ route('reports.show', ['type' => $key, 'from' => $from, 'to' => $to]) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card mb-4">
        <div class="card-body py-2 d-flex flex-wrap justify-content-between align-items-end gap-3">
            <form method="GET" action="{{ route('reports.show', $type) }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small text-muted mb-0">Du</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <label class="form-label small text-muted mb-0">Au</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Actualiser</button>
                </div>
            </form>

            <div class="d-flex gap-2">
                <a href="{{ route('reports.show', ['type' => $type, 'from' => $from, 'to' => $to, 'format' => 'pdf']) }}" target="_blank" class="btn btn-dark btn-sm">
                    <i class="bi bi-file-pdf me-1"></i>Export PDF
                </a>
                <a href="{{ route('reports.show', ['type' => $type, 'from' => $from, 'to' => $to, 'format' => 'csv']) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel (CSV)
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @include('reports.partials.' . $type, ['data' => $data])
        </div>
    </div>
</x-app-layout>
