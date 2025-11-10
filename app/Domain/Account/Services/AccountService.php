<?php

namespace App\Domain\Account\Services;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Transaction\Services\TransactionService;

class AccountService
{
    public function __construct(
        private AccountRepositoryInterface $repository,
        private TransactionService $transactionService
    ) {}

    public function getAccountsWithBalances(int $userId): array
    {
        $accounts = $this->repository->getActiveForUser($userId);

        return $accounts->map(function (Account $account) {
            return [
                'account' => $account,
                'current_balance' => $this->calculateCurrentBalance($account),
                'projected_balance' => $this->calculateProjectedBalance($account),
            ];
        })->toArray();
    }

    public function getArchivedAccountsWithBalances(int $userId): array
    {
        $accounts = $this->repository->getArchivedForUser($userId);

        return $accounts->map(function (Account $account) {
            return [
                'account' => $account,
                'current_balance' => $this->calculateCurrentBalance($account),
                'projected_balance' => $this->calculateProjectedBalance($account),
            ];
        })->toArray();
    }

    public function calculateCurrentBalance(Account $account): float
    {
        // Initial balance is now a transaction (via AccountObserver), so start at 0
        return $this->transactionService->calculateAccountBalance(
            $account->id,
            0,
            now()->format('Y-m-d')
        );
    }

    public function calculateProjectedBalance(Account $account): float
    {
        // Calculate balance including future-dated transactions (no date limit)
        // Initial balance is now a transaction (via AccountObserver), so start at 0
        return $this->transactionService->calculateAccountBalance(
            $account->id,
            0
        );
    }
}
