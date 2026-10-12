<?php

namespace App\Http\Controllers;

use App\Actions\RecordContribution;
use App\Http\Requests\StoreContributionRequest;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\Round;
use App\Models\Tontine;
use App\PaymentMethod;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ContributionController extends Controller
{
    public function store(StoreContributionRequest $request, Tontine $tontine, Round $round, RecordContribution $record): RedirectResponse
    {
        $member = Member::findOrFail($request->integer('member_id'));

        try {
            $contribution = $record->handle(
                $round,
                $member,
                PaymentMethod::from($request->validated('method')),
                CarbonImmutable::parse($request->validated('paid_on')),
                $request->validated('reference'),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $note = $contribution->penalty > 0 ? ' (en retard : pénalité appliquée)' : '';

        return back()->with('success', 'Cotisation de '.$member->name.' enregistrée'.$note.'.');
    }

    public function destroy(Tontine $tontine, Round $round, Contribution $contribution): RedirectResponse
    {
        Gate::authorize('manage', $tontine);
        if ($round->isPaidOut()) {
            return back()->with('error', 'La cagnotte a été remise : ce paiement ne peut plus être annulé.');
        }
        $contribution->delete();

        return back()->with('success', 'Paiement annulé.');
    }
}
