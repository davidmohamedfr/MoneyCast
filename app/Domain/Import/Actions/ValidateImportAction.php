<?php

namespace App\Domain\Import\Actions;

use App\Domain\Import\Data\ImportData;
use App\Domain\Import\Data\ParsedImportData;
use App\Domain\Import\Services\DuplicateDetectionService;

class ValidateImportAction
{
    public function __construct(
        private DuplicateDetectionService $duplicateDetectionService
    ) {}

    public function execute(ImportData $importData, ParsedImportData $parsedData): array
    {
        $errors = [];

        foreach ($parsedData->transactions as $transaction) {
            $rowErrors = [];

            if (empty($transaction->date)) {
                $rowErrors[] = 'Date is required';
            }

            if ($transaction->amount === null && $transaction->debit === null && $transaction->credit === null) {
                $rowErrors[] = 'Amount is required';
            }

            if (empty($transaction->payee)) {
                $rowErrors[] = 'Payee is required';
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $transaction->row_number,
                    'errors' => $rowErrors,
                ];
            }
        }

        return $errors;
    }
}
