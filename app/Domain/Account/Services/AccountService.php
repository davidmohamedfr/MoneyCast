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
        $accounts = $this->repository->getAllForUser($userId);

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
        return $this->transactionService->calculateAccountBalance(
            $account->id,
            (float) $account->initial_balance,
            now()->format('Y-m-d')
        );
    }

    public function calculateProjectedBalance(Account $account): float
    {
        // Calculate balance including future-dated transactions (no date limit)
        return $this->transactionService->calculateAccountBalance(
            $account->id,
            (float) $account->initial_balance
        );
    }
}
