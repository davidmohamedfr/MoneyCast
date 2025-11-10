<?php

namespace App\Domain\Account\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Checking',
            self::Savings => 'Savings',
            self::Credit => 'Credit',
        };
    }
}
