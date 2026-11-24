<?php

namespace App\Enums;

enum ChargeCategory: string
{
    case FABRIC = 'fabric';
    case SEWING = 'sewing';
    case PRINTING = 'printing';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FABRIC => 'Tissu',
            self::SEWING => 'Couture',
            self::PRINTING => 'Impression',
            self::OTHER => 'Autre',
        };
    }
}
