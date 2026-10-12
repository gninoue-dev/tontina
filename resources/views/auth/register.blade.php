<x-layouts.app title="Créer un compte">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-bold">Créer un compte organisateur</h1>
        <p class="mt-1 text-sm text-zinc-600">Les membres de ta tontine n'ont pas besoin de compte : ils suivent tout depuis un lien.</p>
        <form method="POST" action="{{ route('register') }}" class="card mt-6 space-y-4">
            @csrf
            <div>
                <label for="name" class="label">Nom</label>
                <input id="name" name="name" value="{{ old('name') }}" required autofocus class="field" autocomplete="name">
                <x-input-error for="name" />
            </div>
            <div>
                <label for="email" class="label">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="field" autocomplete="email">
                <x-input-error for="email" />
            </div>
            <div>
                <label for="password" class="label">Mot de passe (8 caractères minimum)</label>
                <input id="password" name="password" type="password" required class="field" autocomplete="new-password">
                <x-input-error for="password" />
            </div>
            <div>
                <label for="password_confirmation" class="label">Confirme le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="field" autocomplete="new-password">
            </div>
            <button class="btn-primary w-full">Créer mon compte</button>
            <p class="text-center text-sm text-zinc-500">Déjà inscrit ? <a href="{{ route('login') }}" class="font-medium text-brand-700 hover:underline">Connexion</a></p>
        </form>
    </div>
</x-layouts.app>
