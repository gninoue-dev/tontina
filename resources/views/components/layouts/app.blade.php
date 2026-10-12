@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <meta name="description" content="Tontina : gérez votre tontine sans cahier, en toute transparence.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
    <header class="border-b border-zinc-200 bg-white">
        <nav class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ auth()->check() ? route('tontines.index') : route('home') }}" class="flex items-center gap-2 text-lg font-bold text-brand-700">
                <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-br from-accent-500 to-brand-600 text-white">T</span>
                Tontina
            </a>
            <div class="flex items-center gap-2 text-sm">
                @auth
                    <a href="{{ route('tontines.index') }}" class="hidden font-medium text-zinc-600 hover:text-zinc-900 sm:inline">Mes tontines</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-ghost py-1.5">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost py-1.5">Connexion</a>
                    <a href="{{ route('register') }}" class="btn-primary py-1.5">Créer un compte</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50 px-4 py-3 text-sm text-brand-900" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-5xl px-4 pb-10 text-center text-xs text-zinc-400">
        Tontina · fait en Côte d'Ivoire 🇨🇮 · aucun argent ne transite par l'application
    </footer>
</body>
</html>
