<?php

namespace App\Domain\Account\Repositories;

use App\Domain\Account\Data\AccountData;
use App\Domain\Account\Models\Account;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository implements AccountRepositoryInterface
{
    public function create(AccountData $data): Account
    {
        return Account::create([
            'user_id' => $data->user_id,
            'name' => $data->name,
            'type' => $data->type,
            'bank' => $data->bank,
            'initial_balance' => $data->initial_balance,
            'currency' => $data->currency,
        ]);
    }

    public function update(Account $account, AccountData $data): Account
    {
        $account->update([
            'name' => $data->name,
            'type' => $data->type,
            'bank' => $data->bank,
        ]);

        return $account->fresh();
    }

    public function delete(Account $account): bool
    {
        return $account->delete();
    }

    public function findById(int $id, array $with = []): ?Account
    {
        $query = Account::query();

        if (! empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    public function getAllForUser(int $userId, array $with = []): Collection
    {
        $query = Account::where('user_id', $userId);

        if (! empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getActiveForUser(int $userId, array $with = []): Collection
    {
        $query = Account::where('user_id', $userId)
            ->orderBy('name');

        if (! empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getArchivedForUser(int $userId, array $with = []): Collection
    {
        $query = Account::onlyTrashed()
            ->where('user_id', $userId)
            ->orderBy('name');

        if (! empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getActiveForUserWithFilters(int $userId, array $filters = [], array $with = []): Collection
    {
        $query = Account::where('user_id', $userId);

        // Apply search filter at query level using ILIKE for PostgreSQL (case-insensitive)
        // Falls back to LIKE for other databases, but project uses PostgreSQL
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ILIKE', '%'.$filters['search'].'%')
                    ->orWhere('bank', 'ILIKE', '%'.$filters['search'].'%');
            });
        }

        // Apply type filter
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Apply bank filter
        if (! empty($filters['bank'])) {
            $query->where('bank', $filters['bank']);
        }

        $query->orderBy('name');

        if (! empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function hasTransactions(Account $account): bool
    {
        return $account->transactions()->exists();
    }
}
