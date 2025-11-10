<?php

namespace App\Domain\Account\Data;

use Spatie\LaravelData\Data;

class AccountData extends Data
{
    public function __construct(
        public string $name,
        public string $type,
        public ?float $initial_balance = null,
        public string $currency = 'EUR',
        public ?int $user_id = null,
    ) {}
}
