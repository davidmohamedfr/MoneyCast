<?php

namespace App\Domain\Import\Data;

use Spatie\LaravelData\Data;

class ImportSummaryData extends Data
{
    public function __construct(
        public int $total_rows,
        public int $imported_count,
        public int $skipped_count,
        public int $failed_count,
        public array $errors = [],
    ) {}
}
