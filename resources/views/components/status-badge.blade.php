@props(['status'])
@php
    $raw = $status instanceof \BackedEnum ? $status->value : (string) $status;
    [$label, $class] = match ($raw) {
        'preparing' => ['En préparation', 'bg-warning text-dark'],
        'done' => ['Terminé', 'bg-success'],
        'active' => ['Active', 'bg-success'],
        'validated' => ['Validée', 'bg-success'],
        'valid' => ['Valide', 'bg-success'],
        'cancelled' => ['Annulé(e)', 'bg-danger'],
        '1', 'true' => ['Actif', 'bg-success'],
        '0', 'false' => ['Inactif', 'bg-secondary'],
        default => [ucfirst($raw), 'bg-secondary'],
    };
@endphp
<span {{ $attributes->merge(['class' => "badge {$class}"]) }}>{{ $label }}</span>
