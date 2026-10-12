<x-layouts.app title="Nouvelle tontine">
    <div class="mx-auto max-w-xl">
        <a href="{{ route('tontines.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800">← Mes tontines</a>
        <h1 class="mt-2 text-2xl font-bold">Nouvelle tontine</h1>
        <form method="POST" action="{{ route('tontines.store') }}" class="card mt-6 space-y-4">
            @csrf
            <div>
                <label for="name" class="label">Nom de la tontine</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="field" placeholder="Ex. : Tontine des commerçantes d'Adjamé">
                <x-input-error for="name" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="contribution_amount" class="label">Cotisation par membre (FCFA)</label>
                    <input id="contribution_amount" name="contribution_amount" type="number" min="500" step="500" value="{{ old('contribution_amount', 10000) }}" required class="field">
                    <x-input-error for="contribution_amount" />
                </div>
                <div>
                    <label for="frequency" class="label">Fréquence</label>
                    <select id="frequency" name="frequency" class="field">
                        @foreach ($frequencies as $frequency)
                            <option value="{{ $frequency->value }}" @selected(old('frequency', 'monthly') === $frequency->value)>{{ $frequency->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="frequency" />
                </div>
                <div>
                    <label for="starts_on" class="label">Date du premier tour</label>
                    <input id="starts_on" name="starts_on" type="date" value="{{ old('starts_on', now()->toDateString()) }}" required class="field">
                    <x-input-error for="starts_on" />
                </div>
                <div>
                    <label for="late_penalty" class="label">Pénalité de retard (FCFA)</label>
                    <input id="late_penalty" name="late_penalty" type="number" min="0" step="100" value="{{ old('late_penalty', 0) }}" class="field">
                    <x-input-error for="late_penalty" />
                </div>
            </div>
            <div>
                <label for="description" class="label">Règles / notes (facultatif)</label>
                <textarea id="description" name="description" rows="3" class="field" placeholder="Ex. : cotisation à remettre avant le 5 du mois, réunion chez Awa.">{{ old('description') }}</textarea>
                <x-input-error for="description" />
            </div>
            <button class="btn-primary w-full">Créer la tontine</button>
        </form>
    </div>
</x-layouts.app>
