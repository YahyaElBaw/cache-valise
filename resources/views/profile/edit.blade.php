<x-app-layout>
    <x-slot name="title">Profil administrateur</x-slot>
    <x-slot name="header">Profil & Mot de passe</x-slot>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Informations du compte</div>
                <div class="card-body">
                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success small">Profil mis à jour.</div>
                    @endif
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Modifier le mot de passe</div>
                <div class="card-body">
                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success small">Mot de passe mis à jour.</div>
                    @endif
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
                            @error('current_password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input id="password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required>
                            @error('password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Mettre à jour le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
