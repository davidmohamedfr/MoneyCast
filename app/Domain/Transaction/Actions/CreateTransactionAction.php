<?php

namespace App\Domain\Transaction\Actions;

use App\Domain\Transaction\Data\TransactionData;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class CreateTransactionAction
{
    public function __construct(
        private TransactionRepositoryInterface $repository
    ) {}

    public function execute(TransactionData $data): Transaction
    {
        return $this->repository->create($data);
    }
}
