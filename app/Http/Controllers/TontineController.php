<?php

namespace App\Http\Controllers;

use App\Actions\DrawPayoutOrder;
use App\Actions\StartTontine;
use App\Frequency;
use App\Http\Requests\StoreTontineRequest;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class TontineController extends Controller
{
    public function index(Request $request): View
    {
        $tontines = $request->user()->tontines()
            ->withCount('members')
            ->latest()
            ->orderByDesc('id')
            ->get();

        return view('tontines.index', ['tontines' => $tontines]);
    }

    public function create(): View
    {
        return view('tontines.create', ['frequencies' => Frequency::cases()]);
    }

    public function store(StoreTontineRequest $request): RedirectResponse
    {
        $tontine = $request->user()->tontines()->create([
            ...$request->validated(),
            'late_penalty' => $request->integer('late_penalty'),
        ]);

        return redirect()->route('tontines.show', $tontine)
            ->with('success', 'Tontine créée. Ajoute maintenant les membres.');
    }

    public function show(Tontine $tontine): View
    {
        Gate::authorize('view', $tontine);

        $tontine->load(['members', 'rounds.beneficiary', 'rounds.contributions']);
        $memberCount = $tontine->members->count();
        $currentRound = $tontine->rounds->first(fn ($round) => ! $round->isPaidOut());

        return view('tontines.show', [
            'tontine' => $tontine,
            'currentRound' => $currentRound,
            'pot' => $tontine->contribution_amount * $memberCount,
            'collected' => $tontine->rounds->sum(fn ($round) => $round->contributions->sum('amount')),
            'penalties' => $tontine->rounds->sum(fn ($round) => $round->contributions->sum('penalty')),
            'expected' => $tontine->contribution_amount * $memberCount * $tontine->rounds->count(),
            'publicUrl' => route('public.show', $tontine->public_token),
        ]);
    }

    public function destroy(Tontine $tontine): RedirectResponse
    {
        Gate::authorize('delete', $tontine);
        $tontine->delete();

        return redirect()->route('tontines.index')->with('success', 'Tontine supprimée.');
    }

    public function draw(Tontine $tontine, DrawPayoutOrder $draw): RedirectResponse
    {
        Gate::authorize('manage', $tontine);

        return $this->attempt(fn () => $draw->handle($tontine), $tontine, 'Ordre de passage tiré au sort 🎲');
    }

    public function start(Tontine $tontine, StartTontine $start): RedirectResponse
    {
        Gate::authorize('manage', $tontine);

        return $this->attempt(fn () => $start->handle($tontine), $tontine, 'C\'est parti ! Les tours sont planifiés.');
    }

    /**
     * Exécute une règle métier et transforme son refus en message pour l'organisateur.
     */
    private function attempt(callable $action, Tontine $tontine, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (InvalidArgumentException $e) {
            return redirect()->route('tontines.show', $tontine)->with('error', $e->getMessage());
        }

        return redirect()->route('tontines.show', $tontine)->with('success', $success);
    }
}
