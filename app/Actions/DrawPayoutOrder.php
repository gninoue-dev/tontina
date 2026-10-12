<?php

namespace App\Actions;

use App\Models\Tontine;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DrawPayoutOrder
{
    /**
     * Tire au sort l'ordre de passage des membres (1 = premier à recevoir la cagnotte).
     */
    public function handle(Tontine $tontine): void
    {
        if (! $tontine->isDraft()) {
            throw new InvalidArgumentException("L'ordre de passage ne peut plus changer une fois la tontine démarrée.");
        }

        DB::transaction(function () use ($tontine): void {
            $members = $tontine->members()->get()->shuffle();
            // on libère d'abord les positions pour respecter l'unicité (tontine_id, payout_position)
            $tontine->members()->update(['payout_position' => null]);
            foreach ($members->values() as $index => $member) {
                $member->update(['payout_position' => $index + 1]);
            }
        });
    }
}
