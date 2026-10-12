<?php

namespace App;

use Carbon\CarbonImmutable;

enum Frequency: string
{
    case Weekly = 'weekly';
    case Biweekly = 'biweekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Chaque semaine',
            self::Biweekly => 'Toutes les deux semaines',
            self::Monthly => 'Chaque mois',
        };
    }

    /**
     * Date d'échéance du tour numéro $roundNumber (1 = premier tour, à la date de départ).
     */
    public function dueDate(CarbonImmutable $startsOn, int $roundNumber): CarbonImmutable
    {
        $offset = $roundNumber - 1;

        return match ($this) {
            self::Weekly => $startsOn->addWeeks($offset),
            self::Biweekly => $startsOn->addWeeks(2 * $offset),
            self::Monthly => $startsOn->addMonthsNoOverflow($offset),
        };
    }
}
