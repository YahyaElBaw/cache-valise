<?php

namespace App\Enums;

enum BatchStatus: string
{
    case PREPARING = 'preparing';
    case DONE = 'done';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PREPARING => 'En préparation',
            self::DONE => 'Terminé',
            self::CANCELLED => 'Annulé',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PREPARING => 'bg-warning text-dark',
            self::DONE => 'bg-success',
            self::CANCELLED => 'bg-danger',
        };
    }
}
