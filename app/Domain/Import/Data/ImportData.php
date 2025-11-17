<?php

namespace App\Domain\Import\Data;

use App\Domain\Import\Enums\ImportSource;
use App\Domain\Import\Models\Import;
use Spatie\LaravelData\Data;

class ImportData extends Data
{
    public function __construct(
        public int $user_id,
        public ?int $account_id,
        public ImportSource $source_type,
        public string $file_name,
        public string $file_path,
        public ?array $mapping = null,
    ) {}

    public function toModel(): Import
    {
        return new Import([
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'source_type' => $this->source_type,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'mapping' => $this->mapping,
        ]);
    }
}
