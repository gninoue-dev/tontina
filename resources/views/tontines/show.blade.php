<x-layouts.app :title="$tontine->name">
    <a href="{{ route('tontines.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800">← Mes tontines</a>
    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ $tontine->name }}</h1>
            <p class="mt-1 text-sm text-zinc-600">
                <x-fcfa :amount="$tontine->contribution_amount" /> par membre · {{ mb_strtolower($tontine->frequency->label()) }}
                · à partir du {{ $tontine->starts_on->format('d/m/Y') }}
                @if ($tontine->late_penalty > 0) · pénalité de retard <x-fcfa :amount="$tontine->late_penalty" /> @endif
            </p>
            @if ($tontine->description)
                <p class="mt-2 max-w-2xl text-sm whitespace-pre-line text-zinc-700">{{ $tontine->description }}</p>
            @endif
        </div>
        <span class="rounded-full bg-brand-100 px-3 py-1 text-sm font-medium text-brand-700">{{ $tontine->status->label() }}</span>
    </div>

    <div class="card mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold">🔗 Lien de transparence pour les membres</p>
            <p class="text-xs text-zinc-500">À partager dans le groupe WhatsApp : ordre de passage et cotisations, sans les numéros.</p>
        </div>
        <div class="flex gap-2">
            <input readonly value="{{ $publicUrl }}" class="field w-full font-mono text-xs sm:w-72" onclick="this.select()" aria-label="Lien public">
            <a href="https://wa.me/?text={{ rawurlencode('Suivez notre tontine « '.$tontine->name.' » ici : '.$publicUrl) }}" target="_blank" rel="noopener" class="btn-accent shrink-0">Partager</a>
        </div>
    </div>

    @if ($tontine->isDraft())
        {{-- ── préparation : membres et ordre de passage ── --}}
        <div class="mt-6 grid gap-6 md:grid-cols-5">
            <div class="card md:col-span-3">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Membres ({{ $tontine->members->count() }})</h2>
                    @if ($tontine->members->count() >= 2)
                        <form method="POST" action="{{ route('tontines.draw', $tontine) }}">
                            @csrf
                            <button class="btn-ghost py-1.5">🎲 Tirer au sort l'ordre</button>
                        </form>
                    @endif
                </div>
                @if ($tontine->members->isEmpty())
                    <p class="mt-4 text-sm text-zinc-500">Ajoute les membres à droite. L'ordre de la liste est l'ordre de passage (premier = premier à recevoir la cagnotte).</p>
                @else
                    <ol class="mt-3 divide-y divide-zinc-100">
                        @foreach ($tontine->members as $member)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-7 place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">{{ $member->payout_position ?? $loop->iteration }}</span>
                                    <div>
                                        <p class="text-sm font-medium">{{ $member->name }}</p>
                                        <p class="text-xs text-zinc-500">{{ chunk_split($member->phone, 2, ' ') }}</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('members.destroy', [$tontine, $member]) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-600 hover:underline" aria-label="Retirer {{ $member->name }}">Retirer</button>
                                </form>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
            <div class="space-y-6 md:col-span-2">
                <form method="POST" action="{{ route('members.store', $tontine) }}" class="card space-y-3">
                    @csrf
                    <h2 class="font-semibold">Ajouter un membre</h2>
                    <div>
                        <label for="name" class="label">Nom</label>
                        <input id="name" name="name" value="{{ old('name') }}" required class="field">
                        <x-input-error for="name" />
                    </div>
                    <div>
                        <label for="phone" class="label">Téléphone</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" required class="field" inputmode="tel" placeholder="07 07 12 34 56">
                        <x-input-error for="phone" />
                    </div>
                    <button class="btn-primary w-full">Ajouter</button>
                </form>
                <div class="card space-y-3">
                    <h2 class="font-semibold">Prêt ?</h2>
                    <p class="text-sm text-zinc-600">Au démarrage, Tontina planifie un tour par membre. Les membres et l'ordre ne pourront plus changer.</p>
                    <form method="POST" action="{{ route('tontines.start', $tontine) }}">
                        @csrf
                        <button class="btn-accent w-full" @disabled($tontine->members->count() < 2)>🚀 Démarrer la tontine</button>
                    </form>
                    <form method="POST" action="{{ route('tontines.destroy', $tontine) }}">
                        @csrf @method('DELETE')
                        <button class="btn-danger w-full">Supprimer ce brouillon</button>
                    </form>
                </div>
            </div>
        </div>
    @else
        {{-- ── tontine en cours / terminée ── --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat label="Cagnotte par tour"><x-fcfa :amount="$pot" /></x-stat>
            <x-stat label="Collecté"><x-fcfa :amount="$collected" /></x-stat>
            <x-stat label="Reste à collecter"><x-fcfa :amount="$expected - $collected" /></x-stat>
            <x-stat label="Pénalités"><x-fcfa :amount="$penalties" /></x-stat>
        </div>

        @if ($currentRound)
            <div class="card mt-6 border-brand-500">
                <p class="text-xs font-semibold tracking-wide text-brand-700 uppercase">Tour en cours</p>
                <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-lg font-semibold">Tour n°{{ $currentRound->number }} · pour {{ $currentRound->beneficiary->name }} · échéance {{ $currentRound->due_on->format('d/m/Y') }}</p>
                    <a href="{{ route('rounds.show', [$tontine, $currentRound]) }}" class="btn-primary">Gérer les cotisations</a>
                </div>
            </div>
        @else
            <div class="card mt-6 text-center">
                <p class="text-3xl">🎉</p>
                <p class="mt-1 font-semibold">Tontine terminée : tout le monde a reçu sa cagnotte.</p>
            </div>
        @endif

        <div class="card mt-6 overflow-x-auto">
            <h2 class="font-semibold">Calendrier des tours</h2>
            <table class="mt-3 w-full text-left text-sm">
                <thead class="text-xs text-zinc-500 uppercase">
                    <tr><th class="py-2">Tour</th><th>Bénéficiaire</th><th>Échéance</th><th class="w-40">Cotisations</th><th>Cagnotte</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($tontine->rounds as $round)
                        <tr>
                            <td class="py-2.5"><a href="{{ route('rounds.show', [$tontine, $round]) }}" class="font-medium text-brand-700 hover:underline">n°{{ $round->number }}</a></td>
                            <td>{{ $round->beneficiary->name }}</td>
                            <td @class(['text-red-600 font-medium' => $round->isOverdue()])>{{ $round->due_on->format('d/m/Y') }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-progress :value="$round->contributions->count()" :max="$tontine->members->count()" />
                                    <span class="text-xs text-zinc-500 tabular-nums">{{ $round->contributions->count() }}/{{ $tontine->members->count() }}</span>
                                </div>
                            </td>
                            <td>
                                @if ($round->isPaidOut())
                                    <span class="text-brand-700">✓ remise le {{ $round->paid_out_at->format('d/m') }}</span>
                                @elseif ($round->isOverdue())
                                    <span class="text-red-600">en retard</span>
                                @else
                                    <span class="text-zinc-400">à venir</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>
