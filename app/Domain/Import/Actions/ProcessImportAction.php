<?php

namespace App\Domain\Import\Actions;

use App\Domain\Import\Data\ImportSummaryData;
use App\Domain\Import\Data\ParsedImportData;
use App\Domain\Import\Enums\ImportStatus;
use App\Domain\Import\Models\Import;
use App\Domain\Import\Models\ImportedRow;
use App\Domain\Import\Services\DuplicateDetectionService;
use App\Domain\Transaction\Data\TransactionData;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProcessImportAction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private DuplicateDetectionService $duplicateDetectionService
    ) {}

    public function execute(Import $import, ParsedImportData $parsedData): ImportSummaryData
    {
        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $errors = [];
        $totalTransactions = count($parsedData->transactions);
        $debugLogs = [];

        $this->addLog($debugLogs, "Starting import process for {$totalTransactions} transactions");

        DB::beginTransaction();

        try {
            foreach ($parsedData->transactions as $index => $parsedTransaction) {
                $duplicate = $this->duplicateDetectionService->findDuplicate(
                    $parsedTransaction,
                    $import->account_id,
                    $import->user_id
                );

                if ($duplicate) {
                    $this->addLog($debugLogs, "Row {$parsedTransaction->row_number}: Skipped (duplicate transaction #{$duplicate->id})");
                    ImportedRow::create([
                        'import_id' => $import->id,
                        'row_number' => $parsedTransaction->row_number,
                        'status' => 'skipped',
                        'raw_data' => $parsedTransaction->raw_data,
                        'transaction_id' => $duplicate->id,
                    ]);
                    $skippedCount++;

                    // Update progress every 10 transactions or on last transaction
                    if (($index + 1) % 10 === 0 || $index === $totalTransactions - 1) {
                        $this->addLog($debugLogs, "Progress update: {$importedCount} imported, {$skippedCount} skipped, {$failedCount} failed");
                        $import->update([
                            'imported_rows' => $importedCount,
                            'skipped_rows' => $skippedCount,
                            'failed_rows' => $failedCount,
                            'debug_logs' => $debugLogs,
                        ]);
                    }

                    continue;
                }

                try {
                    $amount = $this->calculateAmount($parsedTransaction);
                    $type = $amount >= 0 ? 'income' : 'expense';
                    $this->addLog($debugLogs, "Row {$parsedTransaction->row_number}: Processing {$type} of {$amount} for {$parsedTransaction->payee}");

                    $transactionData = new TransactionData(
                        account_id: $import->account_id,
                        type: $type,
                        amount: abs($amount),
                        payee: $parsedTransaction->payee,
                        date: $parsedTransaction->date,
                        description: $parsedTransaction->description,
                        user_id: $import->user_id,
                    );

                    $transaction = $this->transactionRepository->create($transactionData);
                    $this->addLog($debugLogs, "Row {$parsedTransaction->row_number}: Created transaction #{$transaction->id}");

                    ImportedRow::create([
                        'import_id' => $import->id,
                        'row_number' => $parsedTransaction->row_number,
                        'status' => 'imported',
                        'raw_data' => $parsedTransaction->raw_data,
                        'transaction_id' => $transaction->id,
                    ]);

                    $importedCount++;

                    // Update progress every 10 transactions or on last transaction
                    if (($index + 1) % 10 === 0 || $index === $totalTransactions - 1) {
                        $this->addLog($debugLogs, "Progress update: {$importedCount} imported, {$skippedCount} skipped, {$failedCount} failed");
                        $import->update([
                            'imported_rows' => $importedCount,
                            'skipped_rows' => $skippedCount,
                            'failed_rows' => $failedCount,
                            'debug_logs' => $debugLogs,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->addLog($debugLogs, "Row {$parsedTransaction->row_number}: FAILED - {$e->getMessage()}");
                    ImportedRow::create([
                        'import_id' => $import->id,
                        'row_number' => $parsedTransaction->row_number,
                        'status' => 'failed',
                        'raw_data' => $parsedTransaction->raw_data,
                        'error_message' => $e->getMessage(),
                    ]);

                    $errors[] = [
                        'row' => $parsedTransaction->row_number,
                        'message' => $e->getMessage(),
                    ];

                    $failedCount++;
                }
            }

            $this->addLog($debugLogs, "Import completed: {$importedCount} imported, {$skippedCount} skipped, {$failedCount} failed");
            $import->update([
                'status' => ImportStatus::COMPLETED,
                'imported_rows' => $importedCount,
                'skipped_rows' => $skippedCount,
                'failed_rows' => $failedCount,
                'completed_at' => now(),
                'debug_logs' => $debugLogs,
            ]);

            DB::commit();

            return new ImportSummaryData(
                total_rows: $parsedData->total_rows,
                imported_count: $importedCount,
                skipped_count: $skippedCount,
                failed_count: $failedCount,
                errors: $errors
            );
        } catch (\Exception $e) {
            DB::rollBack();

            $import->update([
                'status' => ImportStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function calculateAmount($transaction): float
    {
        if ($transaction->amount !== null) {
            return $transaction->amount;
        }

        $debit = $transaction->debit ?? 0;
        $credit = $transaction->credit ?? 0;

        if ($debit > 0) {
            return -abs($debit);
        }

        if ($credit > 0) {
            return abs($credit);
        }

        return 0.0;
    }

    private function addLog(array &$logs, string $message): void
    {
        $logs[] = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'message' => $message,
        ];
    }
}
