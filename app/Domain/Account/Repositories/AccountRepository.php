<?php

namespace App\Domain\Account\Repositories;

use App\Domain\Account\Data\AccountData;
use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Services\TransactionService;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function create(AccountData $data): Account
    {
        return Account::create([
            'user_id' => $data->user_id,
            'name' => $data->name,
            'type' => $data->type,
            'initial_balance' => $data->initial_balance,
            'currency' => $data->currency,
        ]);
    }

    public function update(Account $account, AccountData $data): Account
    {
        $account->update([
            'name' => $data->name,
            'type' => $data->type,
        ]);

        return $account->fresh();
    }

    public function delete(Account $account): bool
    {
        return $account->delete();
    }

    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    public function getAllForUser(int $userId): Collection
    {
        return Account::where('user_id', $userId)->get();
    }

    public function hasTransactions(Account $account): bool
    {
        return $this->transactionService->hasTransactions($account->id);
    }
}
