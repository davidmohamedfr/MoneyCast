<?php

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Data\TransactionData;
use App\Domain\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface
{
    public function create(TransactionData $data): Transaction;

    public function update(Transaction $transaction, TransactionData $data): Transaction;

    public function delete(Transaction $transaction): bool;

    public function findById(int $id): ?Transaction;

    public function getAllForUser(int $userId, ?array $filters = null): Collection;

    public function getAllForAccount(int $accountId, ?array $filters = null): Collection;

    public function getByDateRange(int $accountId, string $startDate, ?string $endDate = null): Collection;

    public function countForAccount(int $accountId): int;
}
