<?php

namespace App\Enums;

enum CashMovementType: string
{
    case OPENING = 'opening';
    case TOP_UP = 'top_up';
    case CUSTOMER_PAYMENT = 'customer_payment';
    case CHARGE_PAYMENT = 'charge_payment';
    case WITHDRAWAL = 'withdrawal';
    case BANK_DEPOSIT = 'bank_deposit';
    case CORRECTION = 'correction';
    case REVERSAL = 'reversal';

    public function label(): string
    {
        return match ($this) {
            self::OPENING => 'Solde initial',
            self::TOP_UP => 'Alimentation',
            self::CUSTOMER_PAYMENT => 'Règlement client',
            self::CHARGE_PAYMENT => 'Paiement charge',
            self::WITHDRAWAL => 'Retrait',
            self::BANK_DEPOSIT => 'Dépôt bancaire',
            self::CORRECTION => 'Correction',
            self::REVERSAL => 'Contre-écriture',
        };
    }

    public static function manualCases(): array
    {
        return [
            self::OPENING,
            self::TOP_UP,
            self::WITHDRAWAL,
            self::BANK_DEPOSIT,
            self::CORRECTION,
        ];
    }
}
