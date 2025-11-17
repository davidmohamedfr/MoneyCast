<?php

namespace App\Domain\Import\Data;

use Spatie\LaravelData\Data;

class ParsedImportData extends Data
{
    public function __construct(
        public array $transactions,
        public array $detected_columns,
        public array $sample_rows,
        public int $total_rows,
    ) {}
}
