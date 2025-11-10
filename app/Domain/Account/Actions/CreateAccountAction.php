<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\Data\AccountData;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountRepositoryInterface;

class CreateAccountAction
{
    public function __construct(
        private AccountRepositoryInterface $repository
    ) {}

    public function execute(AccountData $data): Account
    {
        // Opening balance transaction is now auto-created by AccountObserver
        return $this->repository->create($data);
    }
}
