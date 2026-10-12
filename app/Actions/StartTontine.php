<?php

namespace App\Actions;

use App\Models\Tontine;
use App\TontineStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StartTontine
{
    /**
     * Démarre la tontine : un tour par membre, dans l'ordre de passage, aux dates fixées par la fréquence.
     */
    public function handle(Tontine $tontine): void
    {
        if (! $tontine->isDraft()) {
            throw new InvalidArgumentException('Cette tontine a déjà démarré.');
        }

        $members = $tontine->members()->get();
        if ($members->count() < 2) {
            throw new InvalidArgumentException('Il faut au moins 2 membres pour démarrer une tontine.');
        }

        DB::transaction(function () use ($tontine, $members): void {
            // les membres sans position passent après les autres, dans leur ordre d'inscription
            $tontine->members()->update(['payout_position' => null]);
            foreach ($members->values() as $index => $member) {
                $member->update(['payout_position' => $index + 1]);
                $tontine->rounds()->create([
                    'number' => $index + 1,
                    'due_on' => $tontine->frequency->dueDate($tontine->starts_on, $index + 1),
                    'beneficiary_id' => $member->id,
                ]);
            }
            $tontine->status = TontineStatus::Active;
            $tontine->save();
        });
    }
}
