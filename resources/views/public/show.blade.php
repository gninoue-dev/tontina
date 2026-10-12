<x-layouts.app :title="$tontine->name">
    <div class="text-center">
        <p class="text-xs font-semibold tracking-wide text-brand-700 uppercase">Page de transparence</p>
        <h1 class="mt-1 text-3xl font-bold">{{ $tontine->name }}</h1>
        <p class="mt-2 text-sm text-zinc-600">
            Organisée par {{ $tontine->organizer->name }} · <x-fcfa :amount="$tontine->contribution_amount" /> par membre
            · {{ mb_strtolower($tontine->frequency->label()) }} · {{ $tontine->status->label() }}
        </p>
    </div>

    @if ($tontine->isDraft())
        <div class="card mx-auto mt-6 max-w-xl text-center text-sm text-zinc-600">La tontine n'a pas encore démarré. Membres inscrits : {{ $tontine->members->count() }}.</div>
    @else
        <div class="mt-8 space-y-4">
            @foreach ($tontine->rounds as $round)
                @php($paidIds = $round->contributions->pluck('member_id'))
                <div class="card">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold">Tour n°{{ $round->number }} · {{ $round->beneficiary->name }} reçoit la cagnotte</p>
                        <p class="text-sm text-zinc-500">
                            {{ $round->due_on->format('d/m/Y') }} ·
                            @if ($round->isPaidOut()) <span class="text-brand-700">✓ remise</span>
                            @else {{ $paidIds->count() }}/{{ $tontine->members->count() }} cotisations @endif
                        </p>
                    </div>
                    <x-progress :value="$paidIds->count()" :max="$tontine->members->count()" class="mt-3" />
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($tontine->members as $member)
                            <span @class([
                                'rounded-full px-2.5 py-1 text-xs',
                                'bg-brand-50 text-brand-700' => $paidIds->contains($member->id),
                                'bg-zinc-100 text-zinc-500' => ! $paidIds->contains($member->id),
                            ]) title="{{ $member->maskedPhone() }}">{{ $paidIds->contains($member->id) ? '✓' : '·' }} {{ $member->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <p class="mt-8 text-center text-xs text-zinc-400">Les numéros de téléphone ne sont jamais affichés en entier sur cette page.</p>
</x-layouts.app>
