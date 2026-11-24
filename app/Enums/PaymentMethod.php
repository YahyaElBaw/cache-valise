<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case TRANSFER = 'transfer';
    case CHEQUE = 'cheque';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Espèces',
            self::TRANSFER => 'Virement',
            self::CHEQUE => 'Chèque',
        };
    }
}
