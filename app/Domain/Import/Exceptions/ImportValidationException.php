<?php

namespace App\Domain\Import\Exceptions;

use Exception;

class ImportValidationException extends Exception
{
    public function __construct(
        public array $errors,
        string $message = 'Import validation failed'
    ) {
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
