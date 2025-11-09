<?php

namespace App\Domain\Account\Repositories;

use App\Domain\Account\Data\AccountData;
use App\Domain\Account\Models\Account;
use Illuminate\Database\Eloquent\Collection;

interface AccountRepositoryInterface
{
    public function create(AccountData $data): Account;

    public function update(Account $account, AccountData $data): Account;

    public function delete(Account $account): bool;

    public function findById(int $id): ?Account;

    public function getAllForUser(int $userId): Collection;

    public function hasTransactions(Account $account): bool;
}
