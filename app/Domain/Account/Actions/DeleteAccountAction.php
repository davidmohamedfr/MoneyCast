<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\Exceptions\AccountHasTransactionsException;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountRepositoryInterface;

class DeleteAccountAction
{
    public function __construct(
        private AccountRepositoryInterface $repository
    ) {}

    public function execute(Account $account): bool
    {
        if ($this->repository->hasTransactions($account)) {
            throw new AccountHasTransactionsException($account);
        }

        return $this->repository->delete($account);
    }
}
