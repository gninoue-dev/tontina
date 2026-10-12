<x-layouts.app title="Mes tontines">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Mes tontines</h1>
        <a href="{{ route('tontines.create') }}" class="btn-primary">+ Nouvelle tontine</a>
    </div>

    @if ($tontines->isEmpty())
        <div class="card mt-6 text-center">
            <p class="text-4xl">🤝</p>
            <p class="mt-2 font-semibold">Aucune tontine pour l'instant</p>
            <p class="mt-1 text-sm text-zinc-600">Crée ta première tontine, ajoute les membres, et Tontina s'occupe du reste.</p>
            <a href="{{ route('tontines.create') }}" class="btn-primary mt-4">Créer une tontine</a>
        </div>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach ($tontines as $tontine)
                <a href="{{ route('tontines.show', $tontine) }}" class="card block transition hover:border-brand-500">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="font-semibold">{{ $tontine->name }}</h2>
                        <span @class([
                            'shrink-0 rounded-full px-2 py-0.5 text-xs font-medium',
                            'bg-zinc-100 text-zinc-700' => $tontine->isDraft(),
                            'bg-brand-100 text-brand-700' => $tontine->status === \App\TontineStatus::Active,
                            'bg-accent-400/20 text-accent-600' => $tontine->status === \App\TontineStatus::Completed,
                        ])>{{ $tontine->status->label() }}</span>
                    </div>
                    <p class="mt-2 text-sm text-zinc-600">
                        <x-fcfa :amount="$tontine->contribution_amount" /> · {{ mb_strtolower($tontine->frequency->label()) }} · {{ $tontine->members_count }} membres
                    </p>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
