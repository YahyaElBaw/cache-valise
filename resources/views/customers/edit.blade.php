<x-app-layout>
    <x-slot name="title">Modifier client</x-slot>
    <x-slot name="header">Modifier {{ $customer->name }}</x-slot>

    <div class="card" style="max-width: 680px;"><div class="card-body">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nom *</label><input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">E-mail</label><input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Adresse</label><input type="text" name="address" value="{{ old('address', $customer->address) }}" class="form-control"></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </div></div>
</x-app-layout>
