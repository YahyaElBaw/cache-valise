<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Cache-Valises' }} — Gestion Production & Ventes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: #0f172a;
            color: #cbd5e1;
        }
        .sidebar .nav-link {
            color: #cbd5e1;
            border-radius: .5rem;
            padding: .55rem .85rem;
            font-size: .92rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .sidebar .nav-link.active {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
        }
        .sidebar .nav-section {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            padding: .75rem .85rem .25rem;
        }
        .card {
            border: 0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
            border-radius: .75rem;
        }
        .table th {
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #475569;
            background-color: #f8fafc;
        }
        .money-cell {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <!-- Desktop Sidebar -->
    <aside class="sidebar d-none d-lg-flex flex-column p-3 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none mb-3 px-2">
            <i class="bi bi-luggage-fill fs-4 text-primary"></i>
            <span class="fs-5 fw-bold">Cache-Valises</span>
        </a>
        @include('layouts.sidebar-nav')
    </aside>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start bg-dark text-white d-lg-none" tabindex="-1" id="mobileSidebar" style="width: 260px;">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title d-flex align-items-center gap-2">
                <i class="bi bi-luggage-fill text-primary"></i> Cache-Valises
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body sidebar p-2" style="min-height: auto; width: 100%;">
            @include('layouts.sidebar-nav')
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 d-flex flex-column min-vh-100" style="min-width: 0;">
        <header class="bg-white border-bottom px-3 px-lg-4 py-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h2 class="h5 mb-0 fw-semibold text-dark">{{ $header ?? ($title ?? 'Tableau de bord') }}</h2>
            </div>
            <div class="d-flex align-items-center gap-3">
                @isset($headerActions)
                    <div>{{ $headerActions }}</div>
                @endisset
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none sm:inline">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i>Profil & Mot de passe</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="p-3 p-lg-4 flex-grow-1">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-x-octagon-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-exclamation-circle-fill me-2"></i>Erreurs de saisie :</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[method="POST"]').forEach(form => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.dataset.allowMulti) {
                btn.disabled = true;
                setTimeout(() => { btn.disabled = false; }, 5000);
            }
        });
    });
});
</script>
@stack('scripts')
</body>
</html>
