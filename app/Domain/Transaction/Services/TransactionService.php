<?php

namespace App\Domain\Transaction\Services;

use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function getPaginatedTransactionsForAccount(int $accountId, ?array $filters = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getPaginatedForAccount($accountId, $filters, $perPage);
    }

    public function calculateAccountBalance(int $accountId, float $initialBalance, ?string $upToDate = null): float
    {
        $filters = $upToDate ? ['end_date' => $upToDate] : null;
        $transactions = $this->repository->getAllForAccount($accountId, $filters);

        $balance = $initialBalance;

        foreach ($transactions as $transaction) {
            match ($transaction->type) {
                TransactionType::Income => $balance += (float) $transaction->amount,
                TransactionType::Expense => $balance -= (float) $transaction->amount,
                TransactionType::Transfer => $balance -= (float) $transaction->amount,
                default => null,
            };
        }

        return (float) round($balance, 4);
    }

    public function hasTransactions(int $accountId): bool
    {
        return $this->repository->countForAccount($accountId) > 0;
    }

    public function calculateAccountStats(int $accountId): array
    {
        $transactions = $this->repository->getAllForAccount($accountId);

        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($transactions as $transaction) {
            match ($transaction->type) {
                TransactionType::Income => $totalIncome += (float) $transaction->amount,
                TransactionType::Expense => $totalExpenses += (float) $transaction->amount,
                default => null,
            };
        }

        return [
            'total_income' => (float) round($totalIncome, 4),
            'total_expenses' => (float) round($totalExpenses, 4),
        ];
    }
}
