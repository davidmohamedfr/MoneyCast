<?php

namespace App\Domain\Transaction\Data;

use Spatie\LaravelData\Data;

class TransactionData extends Data
{
    public function __construct(
        public int $account_id,
        public string $type,
        public float $amount,
        public string $payee,
        public string $date,
        public ?int $category_id = null,
        public ?string $description = null,
        public ?string $notes = null,
        public ?int $related_transaction_id = null,
        public ?int $user_id = null,
        public bool $is_opening_balance = false,
    ) {}
}
