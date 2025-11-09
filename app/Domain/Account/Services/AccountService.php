<?php

namespace App\Domain\Account\Services;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountRepositoryInterface;

class AccountService
{
    public function __construct(
        private AccountRepositoryInterface $repository
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
        // This will be fully implemented when Transaction domain is created
        // For now, return initial balance
        return (float) $account->initial_balance;
    }

    public function calculateProjectedBalance(Account $account): float
    {
        // This will be fully implemented when Transaction domain is created
        // For now, return initial balance
        return (float) $account->initial_balance;
    }
}
