<?php

namespace App\Domain\Import\Contracts;

use App\Domain\Import\Data\ParsedImportData;
use App\Domain\Import\Enums\ImportSource;

interface ImportParserInterface
{
    public function parse(string $filePath, ?array $mapping = null): ParsedImportData;

    public function validate(string $filePath): bool;

    public function getSupportedSource(): ImportSource;
}
