<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Connexion' }} — Cache-Valises</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            width: 100%;
            max-width: 430px;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="card auth-card mx-auto">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-2" style="width: 56px; height: 56px;">
                        <i class="bi bi-luggage-fill fs-3"></i>
                    </div>
                    <h1 class="h4 fw-bold mb-1">Gestion Cache-Valises</h1>
                    <p class="text-muted small mb-0">Production par lots • Ventes • Caisse</p>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
