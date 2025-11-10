<?php

namespace App\Domain\Transaction\Services;

use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    public function __construct(
        private TransactionRepositoryInterface $repository
    ) {}

    public function getTransactionsForUser(int $userId, ?array $filters = null): Collection
    {
        return $this->repository->getAllForUser($userId, $filters);
    }

    public function getTransactionsForAccount(int $accountId, ?array $filters = null): Collection
    {
        return $this->repository->getAllForAccount($accountId, $filters);
    }

    public function calculateAccountBalance(int $accountId, float $initialBalance, ?string $upToDate = null): float
    {
        $filters = $upToDate ? ['end_date' => $upToDate] : null;
        $transactions = $this->repository->getAllForAccount($accountId, $filters);

        $balance = $initialBalance;

        foreach ($transactions as $transaction) {
            match ($transaction->type) {
                'income' => $balance += (float) $transaction->amount,
                'expense' => $balance -= (float) $transaction->amount,
                'transfer' => $balance -= (float) $transaction->amount,
                default => null,
            };
        }

        return $balance;
    }

    public function hasTransactions(int $accountId): bool
    {
        return $this->repository->countForAccount($accountId) > 0;
    }
}
