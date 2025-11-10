<?php

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Data\TransactionData;
use App\Domain\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(TransactionData $data): Transaction
    {
        return Transaction::create([
            'user_id' => $data->user_id,
            'account_id' => $data->account_id,
            'category_id' => $data->category_id,
            'type' => $data->type,
            'amount' => $data->amount,
            'payee' => $data->payee,
            'description' => $data->description,
            'date' => $data->date,
            'notes' => $data->notes,
            'related_transaction_id' => $data->related_transaction_id,
        ]);
    }

    public function update(Transaction $transaction, TransactionData $data): Transaction
    {
        $transaction->update([
            'account_id' => $data->account_id,
            'category_id' => $data->category_id,
            'type' => $data->type,
            'amount' => $data->amount,
            'payee' => $data->payee,
            'description' => $data->description,
            'date' => $data->date,
            'notes' => $data->notes,
        ]);

        return $transaction->fresh();
    }

    public function delete(Transaction $transaction): bool
    {
        return $transaction->delete();
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::with(['account', 'category', 'relatedTransaction'])->find($id);
    }

    public function getAllForUser(int $userId, ?array $filters = null): Collection
    {
        $query = Transaction::with(['account', 'category'])
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($filters) {
            if (isset($filters['account_id'])) {
                $query->where('account_id', $filters['account_id']);
            }

            if (isset($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['start_date'])) {
                $query->where('date', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])) {
                $query->where('date', '<=', $filters['end_date']);
            }
        }

        return $query->get();
    }

    public function getAllForAccount(int $accountId, ?array $filters = null): Collection
    {
        $query = Transaction::with(['category'])
            ->where('account_id', $accountId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($filters) {
            if (isset($filters['start_date'])) {
                $query->where('date', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])) {
                $query->where('date', '<=', $filters['end_date']);
            }
        }

        return $query->get();
    }

    public function getByDateRange(int $accountId, string $startDate, ?string $endDate = null): Collection
    {
        $query = Transaction::where('account_id', $accountId)
            ->where('date', '>=', $startDate);

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->orderBy('date')->get();
    }

    public function countForAccount(int $accountId): int
    {
        return Transaction::where('account_id', $accountId)->count();
    }
}
