<?php

namespace App;

enum TontineStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'En préparation',
            self::Active => 'En cours',
            self::Completed => 'Terminée',
        };
    }
}
