<?php

namespace App\Support;

class Fcfa
{
    /**
     * 25000 → « 25 000 FCFA » (espaces insécables, comme on l'écrit en Côte d'Ivoire).
     */
    public static function format(int $amount): string
    {
        return number_format($amount, 0, ',', "\u{202F}")."\u{00A0}FCFA";
    }
}
