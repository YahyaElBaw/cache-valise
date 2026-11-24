<x-guest-layout>
    <p class="text-muted small mb-3">
        Indiquez votre adresse e-mail pour recevoir un lien de réinitialisation du mot de passe.
    </p>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="small text-decoration-none">Retour à la connexion</a>
        </div>
    </form>
</x-guest-layout>
