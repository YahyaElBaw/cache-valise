<ul class="nav flex-column gap-1">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
    </li>

    <li class="nav-section">Production</li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('product-models.*') ? 'active' : '' }}" href="{{ route('product-models.index') }}">
            <i class="bi bi-tags"></i> Modèles
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('batches.*') ? 'active' : '' }}" href="{{ route('batches.index') }}">
            <i class="bi bi-boxes"></i> Lots
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('charges.*') ? 'active' : '' }}" href="{{ route('charges.index') }}">
            <i class="bi bi-receipt"></i> Charges
        </a>
    </li>

    <li class="nav-section">Commercial</li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
            <i class="bi bi-people"></i> Clients
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
            <i class="bi bi-truck"></i> Fournisseurs
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
            <i class="bi bi-file-earmark-text"></i> Ventes & Factures
        </a>
    </li>

    <li class="nav-section">Trésorerie & Suivi</li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
            <i class="bi bi-credit-card"></i> Règlements
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cash.*') ? 'active' : '' }}" href="{{ route('cash.index') }}">
            <i class="bi bi-cash-stack"></i> Caisse espèces
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.show', 'charges') }}">
            <i class="bi bi-bar-chart-line"></i> Rapports
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}" href="{{ route('activity-logs.index') }}">
            <i class="bi bi-clock-history"></i> Historique
        </a>
    </li>
</ul>
