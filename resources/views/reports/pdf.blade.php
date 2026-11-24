<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport {{ ucfirst($type) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .sub { color: #64748b; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; }
        th { background: #f1f5f9; text-align: left; font-size: 10px; text-transform: uppercase; }
        .text-end, .text-right { text-align: right; }
        .fw-bold, .fw-semibold { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Rapport : {{ ucfirst($type) }}</h1>
    <div class="sub">Période : {{ $from }} au {{ $to }} — Édité le {{ now()->format('d/m/Y H:i') }}</div>
    @include('reports.partials.' . $type, ['data' => $data])
</body>
</html>
