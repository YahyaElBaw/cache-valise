<x-guest-layout>
    <p class="text-muted small mb-3">
        Zone sécurisée : veuillez confirmer votre mot de passe avant de continuer.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Mot de passe</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Confirmer</button>
    </form>
</x-guest-layout>
