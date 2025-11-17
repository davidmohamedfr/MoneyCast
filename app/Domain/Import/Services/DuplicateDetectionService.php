<?php

namespace App\Domain\Import\Services;

use App\Domain\Import\Data\ParsedTransactionData;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class DuplicateDetectionService
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    public function findDuplicate(
        ParsedTransactionData $transaction,
        int $accountId,
        int $userId
    ): ?Transaction {
        $amount = $this->calculateAmount($transaction);

        return $this->transactionRepository->findDuplicate(
            userId: $userId,
            accountId: $accountId,
            date: $transaction->date,
            amount: $amount,
            payee: $transaction->payee,
            description: $transaction->description
        );
    }

    private function calculateAmount(ParsedTransactionData $transaction): float
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
}
