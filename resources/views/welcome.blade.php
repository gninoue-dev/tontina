<x-layouts.app>
    <section class="grid items-center gap-10 py-6 md:grid-cols-2">
        <div>
            <p class="mb-3 inline-block rounded-full bg-accent-400/20 px-3 py-1 text-xs font-semibold text-accent-600">Fini le cahier de tontine</p>
            <h1 class="text-4xl leading-tight font-bold tracking-tight text-zinc-900 md:text-5xl">
                Ta tontine, <span class="text-brand-600">claire pour tout le monde.</span>
            </h1>
            <p class="mt-4 text-lg text-zinc-600">
                Qui a cotisé ? Qui reçoit la cagnotte ce mois-ci ? Combien de retards ?
                Tontina répond à la place du cahier, et chaque membre peut vérifier depuis son téléphone.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn-primary px-6 py-3 text-base">Créer ma tontine</a>
                <a href="{{ route('login') }}" class="btn-ghost px-6 py-3 text-base">J'ai déjà un compte</a>
            </div>
        </div>
        <div class="card space-y-3 bg-gradient-to-br from-white to-brand-50">
            <div class="flex items-center justify-between">
                <p class="font-semibold">Tontine des collègues · tour 3/8</p>
                <span class="rounded-full bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-700">En cours</span>
            </div>
            <x-progress :value="6" :max="8" />
            <p class="text-sm text-zinc-600">6 cotisations sur 8 · cagnotte : <x-fcfa :amount="200000" class="font-semibold text-zinc-900" /></p>
            <ul class="divide-y divide-zinc-100 text-sm">
                <li class="flex justify-between py-2"><span>Awa K.</span><span class="text-brand-700">✓ Wave</span></li>
                <li class="flex justify-between py-2"><span>Yao B.</span><span class="text-brand-700">✓ Orange Money</span></li>
                <li class="flex justify-between py-2"><span>Koffi N.</span><span class="text-accent-600">⏳ en attente · rappel WhatsApp</span></li>
            </ul>
        </div>
    </section>

    <section class="mt-12 grid gap-4 md:grid-cols-3">
        <div class="card">
            <p class="text-2xl">📒</p>
            <h2 class="mt-2 font-semibold">Le problème</h2>
            <p class="mt-1 text-sm text-zinc-600">Les tontines se tiennent souvent dans un cahier ou un groupe WhatsApp : erreurs de calcul, oublis, disputes sur qui a payé, et tout repose sur la mémoire de l'organisateur.</p>
        </div>
        <div class="card">
            <p class="text-2xl">✅</p>
            <h2 class="mt-2 font-semibold">Ce que fait Tontina</h2>
            <p class="mt-1 text-sm text-zinc-600">Ordre de passage tiré au sort, échéances calculées, cotisations en espèces ou Mobile Money (Orange, MTN, Moov, Wave), pénalités de retard, rappels WhatsApp en un clic.</p>
        </div>
        <div class="card">
            <p class="text-2xl">🔍</p>
            <h2 class="mt-2 font-semibold">La transparence</h2>
            <p class="mt-1 text-sm text-zinc-600">Chaque tontine a un lien à partager : les membres voient l'ordre de passage et qui a cotisé, sans les numéros de téléphone. Tontina ne touche jamais à l'argent.</p>
        </div>
    </section>
</x-layouts.app>
