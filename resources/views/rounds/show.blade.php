<x-layouts.app :title="'Tour n°'.$round->number.' · '.$tontine->name">
    <a href="{{ route('tontines.show', $tontine) }}" class="text-sm text-zinc-500 hover:text-zinc-800">← {{ $tontine->name }}</a>
    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Tour n°{{ $round->number }} · pour {{ $round->beneficiary->name }}</h1>
            <p class="mt-1 text-sm text-zinc-600">
                Échéance {{ $round->due_on->format('d/m/Y') }}
                @if ($round->isOverdue()) · <span class="font-medium text-red-600">en retard</span> @endif
                · {{ $paid->count() }}/{{ $members->count() }} cotisations
            </p>
        </div>
        @if ($round->isPaidOut())
            <span class="rounded-full bg-brand-100 px-3 py-1 text-sm font-medium text-brand-700">✓ Cagnotte remise le {{ $round->paid_out_at->format('d/m/Y') }}</span>
        @elseif ($late->isEmpty())
            <form method="POST" action="{{ route('rounds.payout', [$tontine, $round]) }}">
                @csrf
                <button class="btn-accent">💰 Remettre la cagnotte à {{ $round->beneficiary->name }}</button>
            </form>
        @endif
    </div>
    <x-progress :value="$paid->count()" :max="$members->count()" class="mt-4" />

    <div class="mt-6 grid gap-6 md:grid-cols-5">
        <div class="card md:col-span-3">
            <h2 class="font-semibold">Cotisations</h2>
            <ul class="mt-3 divide-y divide-zinc-100">
                @foreach ($members as $member)
                    @php($contribution = $paid->get($member->id))
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                        <div>
                            <p class="text-sm font-medium">{{ $member->name }} @if ($member->is($round->beneficiary)) <span class="text-xs text-accent-600">(bénéficiaire)</span> @endif</p>
                            @if ($contribution)
                                <p class="text-xs text-zinc-500">
                                    {{ $contribution->method->label() }} · le {{ $contribution->paid_on->format('d/m/Y') }}
                                    @if ($contribution->reference) · réf. {{ $contribution->reference }} @endif
                                    @if ($contribution->penalty > 0) · <span class="text-red-600">pénalité <x-fcfa :amount="$contribution->penalty" /></span> @endif
                                </p>
                            @endif
                        </div>
                        @if ($contribution)
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-brand-700">✓ <x-fcfa :amount="$contribution->amount" /></span>
                                @unless ($round->isPaidOut())
                                    <form method="POST" action="{{ route('contributions.destroy', [$tontine, $round, $contribution]) }}">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-zinc-400 hover:text-red-600" aria-label="Annuler le paiement de {{ $member->name }}">Annuler</button>
                                    </form>
                                @endunless
                            </div>
                        @else
                            <a href="{{ $reminder($member) }}" target="_blank" rel="noopener" class="btn-ghost py-1 text-xs">💬 Rappel WhatsApp</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        @unless ($round->isPaidOut() || $late->isEmpty())
            <form method="POST" action="{{ route('contributions.store', [$tontine, $round]) }}" class="card space-y-3 self-start md:col-span-2">
                @csrf
                <h2 class="font-semibold">Enregistrer un paiement</h2>
                <div>
                    <label for="member_id" class="label">Membre</label>
                    <select id="member_id" name="member_id" class="field">
                        @foreach ($late as $member)
                            <option value="{{ $member->id }}" @selected((int) old('member_id') === $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="member_id" />
                </div>
                <div>
                    <label for="method" class="label">Moyen de paiement</label>
                    <select id="method" name="method" class="field">
                        @foreach ($methods as $method)
                            <option value="{{ $method->value }}" @selected(old('method') === $method->value)>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="method" />
                </div>
                <div>
                    <label for="reference" class="label">Référence Mobile Money (facultatif)</label>
                    <input id="reference" name="reference" value="{{ old('reference') }}" class="field" placeholder="Ex. : PP231012.1405.A12345">
                    <x-input-error for="reference" />
                </div>
                <div>
                    <label for="paid_on" class="label">Date du paiement</label>
                    <input id="paid_on" name="paid_on" type="date" value="{{ old('paid_on', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="field">
                    <x-input-error for="paid_on" />
                </div>
                <p class="text-xs text-zinc-500">Montant : <x-fcfa :amount="$tontine->contribution_amount" />
                    @if ($tontine->late_penalty > 0) · après le {{ $round->due_on->format('d/m') }}, pénalité de <x-fcfa :amount="$tontine->late_penalty" /> @endif
                </p>
                <button class="btn-primary w-full">Enregistrer</button>
            </form>
        @endunless
    </div>
</x-layouts.app>
