<?php

namespace App\Domain\Import\Exceptions;

use App\Domain\Import\Enums\ImportSource;
use Exception;

class ParserNotFoundException extends Exception
{
    public function __construct(ImportSource $source)
    {
        parent::__construct("Parser not found for source type: {$source->value}");
    }
}
