@props([
    'action',
    'label' => 'Annuler',
    'message' => 'Voulez-vous vraiment annuler cette opération ? Une contre-écriture sera générée si applicable.',
    'buttonClass' => 'btn btn-outline-danger btn-sm',
])
@php
    $modalId = 'confirmCancel_' . md5($action . $label . uniqid('', true));
@endphp
<button type="button" class="{{ $buttonClass }}" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
    <i class="bi bi-x-circle me-1"></i>{{ $label }}
</button>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Confirmer l’annulation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-start">
                <p class="mb-0">{{ $message }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
                <form method="POST" action="{{ $action }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Confirmer l’annulation</button>
                </form>
            </div>
        </div>
    </div>
</div>
