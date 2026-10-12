<x-layouts.app title="Connexion">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-bold">Connexion</h1>
        <form method="POST" action="{{ route('login') }}" class="card mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="label">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="field" autocomplete="email">
                <x-input-error for="email" />
            </div>
            <div>
                <label for="password" class="label">Mot de passe</label>
                <input id="password" name="password" type="password" required class="field" autocomplete="current-password">
            </div>
            <label class="flex items-center gap-2 text-sm text-zinc-600">
                <input type="checkbox" name="remember" class="rounded border-zinc-300 text-brand-600"> Rester connecté
            </label>
            <button class="btn-primary w-full">Se connecter</button>
            <p class="text-center text-sm text-zinc-500">Pas encore de compte ? <a href="{{ route('register') }}" class="font-medium text-brand-700 hover:underline">Créer un compte</a></p>
        </form>
    </div>
</x-layouts.app>
