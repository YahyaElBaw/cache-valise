<x-app-layout>
    <x-slot name="title">Tableau de bord</x-slot>
    <x-slot name="header">Tableau de bord</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle facture
        </a>
    </x-slot>

    <!-- Period filter -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small text-muted mb-0">Du</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <label class="form-label small text-muted mb-0">Au</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer la période</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-link btn-sm text-decoration-none">Mois en cours</a>
                </div>
            </form>
        </div>
    </div>

    <!-- 4 KPI cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Chiffre d’affaires</div>
                    <div class="fs-4 fw-bold text-primary mt-1"><x-money :value="$revenue" /></div>
                    <div class="small text-muted mt-1">COGS : <x-money :value="$cogs" /></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Marge brute</div>
                    <div class="fs-4 fw-bold text-success mt-1"><x-money :value="$grossMargin" /></div>
                    <div class="small text-muted mt-1">Période sélectionnée</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Créances clients</div>
                    <div class="fs-4 fw-bold text-warning mt-1"><x-money :value="$receivables" /></div>
                    <div class="small text-muted mt-1">Total restant dû</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Solde de caisse</div>
                    <div class="fs-4 fw-bold text-info mt-1"><x-money :value="$cashBalance" /></div>
                    <div class="small text-muted mt-1">Espèces disponibles</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Chart.js charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Ventes vs Charges par mois</div>
                <div class="card-body">
                    <canvas id="revenueChargesChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Charges par catégorie</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="categoryDoughnutChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick tables -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Dernières ventes</span>
                    <a href="{{ route('sales.index') }}" class="small text-decoration-none">Tout voir</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Facture</th><th>Client</th><th>Date</th><th class="text-end">Total</th><th>Statut</th></tr></thead>
                        <tbody>
                        @forelse ($recentSales as $sale)
                            <tr>
                                <td><a href="{{ route('sales.show', $sale) }}" class="fw-semibold text-decoration-none">{{ $sale->invoice_number }}</a></td>
                                <td>{{ $sale->customer?->name ?? 'Vente comptoir' }}</td>
                                <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                                <td class="text-end"><x-money :value="$sale->total" /></td>
                                <td><x-status-badge :status="$sale->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Aucune vente.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Dernières actions</span>
                    <a href="{{ route('activity-logs.index') }}" class="small text-decoration-none">Historique</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recentLogs as $log)
                        <li class="list-group-item small d-flex justify-content-between align-items-start">
                            <span>{{ $log->description }}</span>
                            <span class="text-muted ms-2 text-nowrap">{{ $log->created_at?->format('d/m H:i') }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-3">Aucune activité.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const barCtx = document.getElementById('revenueChargesChart');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: @json($monthsLabels),
                    datasets: [
                        {
                            label: 'Ventes (DT)',
                            data: @json($monthlyRevenue),
                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                            borderRadius: 4
                        },
                        {
                            label: 'Charges (DT)',
                            data: @json($monthlyCharges),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        const pieCtx = document.getElementById('categoryDoughnutChart');
        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($categoryLabels),
                    datasets: [{
                        data: @json($categoryTotals),
                        backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#64748b']
                    }]
                },
                options: { responsive: true }
            });
        }
    });
    </script>
    @endpush
</x-app-layout>
