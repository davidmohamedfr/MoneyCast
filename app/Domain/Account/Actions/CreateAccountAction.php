<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\Data\AccountData;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateAccountAction
{
    public function __construct(
        private AccountRepositoryInterface $repository
    ) {}

    public function execute(AccountData $data): Account
    {
        return DB::transaction(function () use ($data) {
            // Opening balance transaction is auto-created by AccountObserver
            // Both account creation and opening balance transaction are atomic
            return $this->repository->create($data);
        });
    }
}
