<?php

namespace App\Actions;

use App\Models\Contribution;
use App\Models\Member;
use App\Models\Round;
use App\PaymentMethod;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class RecordContribution
{
    /**
     * Enregistre la cotisation d'un membre pour un tour, avec la pénalité si elle arrive après l'échéance.
     */
    public function handle(Round $round, Member $member, PaymentMethod $method, CarbonImmutable $paidOn, ?string $reference = null): Contribution
    {
        $tontine = $round->tontine;

        if ((int) $member->tontine_id !== (int) $round->tontine_id) {
            throw new InvalidArgumentException('Ce membre ne fait pas partie de cette tontine.');
        }
        if ($round->isPaidOut()) {
            throw new InvalidArgumentException('La cagnotte de ce tour a déjà été remise.');
        }
        if ($round->contributions()->whereBelongsTo($member)->exists()) {
            throw new InvalidArgumentException("{$member->name} a déjà cotisé pour ce tour.");
        }

        return $round->contributions()->create([
            'member_id' => $member->id,
            'amount' => $tontine->contribution_amount,
            'penalty' => $paidOn->greaterThan($round->due_on) ? $tontine->late_penalty : 0,
            'method' => $method,
            'reference' => $method->isMobileMoney() ? $reference : null,
            'paid_on' => $paidOn,
        ]);
    }
}
