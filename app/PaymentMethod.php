<?php

namespace App;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case OrangeMoney = 'orange_money';
    case MtnMomo = 'mtn_momo';
    case MoovMoney = 'moov_money';
    case Wave = 'wave';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Espèces',
            self::OrangeMoney => 'Orange Money',
            self::MtnMomo => 'MTN MoMo',
            self::MoovMoney => 'Moov Money',
            self::Wave => 'Wave',
        };
    }

    public function isMobileMoney(): bool
    {
        return $this !== self::Cash;
    }
}
