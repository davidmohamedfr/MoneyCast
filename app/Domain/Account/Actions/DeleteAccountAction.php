<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountRepositoryInterface;
use Exception;

class DeleteAccountAction
{
    public function __construct(
        private AccountRepositoryInterface $repository
    ) {}

    public function execute(Account $account): bool
    {
        if ($this->repository->hasTransactions($account)) {
            throw new Exception('Cannot delete account with transactions');
        }

        return $this->repository->delete($account);
    }
}
