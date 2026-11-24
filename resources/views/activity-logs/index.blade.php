<x-app-layout>
    <x-slot name="title">Historique</x-slot>
    <x-slot name="header">Journal d’activité</x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date / Heure</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>Utilisateur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td class="small text-muted">{{ class_basename($log->subject_type) }} {{ $log->subject_id ? '#' . $log->subject_id : '' }}</td>
                            <td>{{ $log->description }}</td>
                            <td class="small">{{ $log->user?->name ?? 'Système' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune activité enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
