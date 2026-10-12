<?php

namespace App\Actions;

use App\Models\Round;
use App\TontineStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PayOutRound
{
    /**
     * Marque la cagnotte du tour comme remise au bénéficiaire. La tontine se termine après le dernier tour.
     */
    public function handle(Round $round): void
    {
        if ($round->isPaidOut()) {
            throw new InvalidArgumentException('Cette cagnotte a déjà été remise.');
        }

        $tontine = $round->tontine;
        $expected = $tontine->members()->count();
        if ($round->contributions()->count() < $expected) {
            throw new InvalidArgumentException('Tous les membres doivent avoir cotisé avant de remettre la cagnotte.');
        }

        DB::transaction(function () use ($round, $tontine): void {
            $round->update(['paid_out_at' => now()]);
            if ($tontine->rounds()->whereNull('paid_out_at')->doesntExist()) {
                $tontine->status = TontineStatus::Completed;
                $tontine->save();
            }
        });
    }
}
