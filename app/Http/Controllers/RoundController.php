<?php

namespace App\Http\Controllers;

use App\Actions\PayOutRound;
use App\Models\Round;
use App\Models\Tontine;
use App\PaymentMethod;
use App\Support\Fcfa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class RoundController extends Controller
{
    public function show(Tontine $tontine, Round $round): View
    {
        Gate::authorize('view', $tontine);

        $round->load(['beneficiary', 'contributions.member']);
        $paid = $round->contributions->keyBy('member_id');
        $members = $tontine->members()->get();
        $late = $members->reject(fn ($member) => $paid->has($member->id));

        $reminder = fn ($member) => $member->whatsappLink(
            "Bonjour {$member->name}, petit rappel de la tontine « {$tontine->name} » : "
            .'ta cotisation de '.Fcfa::format($tontine->contribution_amount)
            ." pour le tour n°{$round->number} (au profit de {$round->beneficiary->name}) "
            .'était attendue le '.$round->due_on->format('d/m/Y').'. Merci 🙏'
        );

        return view('rounds.show', [
            'tontine' => $tontine,
            'round' => $round,
            'members' => $members,
            'paid' => $paid,
            'late' => $late,
            'reminder' => $reminder,
            'methods' => PaymentMethod::cases(),
        ]);
    }

    public function payout(Tontine $tontine, Round $round, PayOutRound $payOut): RedirectResponse
    {
        Gate::authorize('manage', $tontine);

        try {
            $payOut->handle($round);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tontines.show', $tontine)
            ->with('success', 'Cagnotte du tour n°'.$round->number.' remise à '.$round->beneficiary->name.' 🎉');
    }
}
