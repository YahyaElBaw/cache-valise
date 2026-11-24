<x-app-layout>
    <x-slot name="title">Nouveau client</x-slot>
    <x-slot name="header">Nouveau client</x-slot>

    <div class="card" style="max-width: 680px;"><div class="card-body">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nom *</label><input type="text" name="name" value="{{ old('name') }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="phone" value="{{ old('phone') }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">E-mail</label><input type="email" name="email" value="{{ old('email') }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Adresse</label><input type="text" name="address" value="{{ old('address') }}" class="form-control"></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div></div>
</x-app-layout>
