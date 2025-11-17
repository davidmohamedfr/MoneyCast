<?php

namespace App\Domain\Import\Data;

use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class ParsedTransactionData extends Data
{
    public function __construct(
        #[Required, Date]
        public string $date,

        #[Numeric]
        public ?float $amount = null,

        #[Numeric]
        public ?float $debit = null,

        #[Numeric]
        public ?float $credit = null,

        #[Required]
        public string $payee,

        public ?string $description = null,

        #[Required]
        public int $row_number,

        #[Required]
        public array $raw_data,
    ) {}
}
