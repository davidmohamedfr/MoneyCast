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

    public function findById(int $id, array $with = []): ?Account;

    public function getAllForUser(int $userId, array $with = []): Collection;

    public function getActiveForUser(int $userId, array $with = []): Collection;

    public function getArchivedForUser(int $userId, array $with = []): Collection;

    public function getActiveForUserWithFilters(int $userId, array $filters = [], array $with = []): Collection;

    public function hasTransactions(Account $account): bool;
}
