<?php

namespace App\Domain\Transaction\Actions;

use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class DeleteTransactionAction
{
    public function __construct(
        private TransactionRepositoryInterface $repository
    ) {}

    public function execute(Transaction $transaction): bool
    {
        // If this is a transfer, also delete the related transaction
        if ($transaction->type === 'transfer' && $transaction->related_transaction_id) {
            $relatedTransaction = $this->repository->findById($transaction->related_transaction_id);
            if ($relatedTransaction) {
                $this->repository->delete($relatedTransaction);
            }
        }

        return $this->repository->delete($transaction);
    }
}
