<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\Data\AccountData;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountRepositoryInterface;

class UpdateAccountAction
{
    public function __construct(
        private AccountRepositoryInterface $repository
    ) {}

    public function execute(Account $account, AccountData $data): Account
    {
        return $this->repository->update($account, $data);
    }
}
