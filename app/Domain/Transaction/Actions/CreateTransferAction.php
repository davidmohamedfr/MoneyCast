<?php

namespace App\Domain\Transaction\Actions;

use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Transaction\Data\TransactionData;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateTransferAction
{
    public function __construct(
        private TransactionRepositoryInterface $repository,
        private AccountRepositoryInterface $accountRepository
    ) {}

    public function execute(
        int $fromAccountId,
        int $toAccountId,
        float $amount,
        string $date,
        int $userId,
        ?string $description = null,
        ?string $notes = null
    ): array {
        // Validate amount
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Transfer amount must be greater than zero');
        }

        // Validate date format (Y-m-d)
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if (! $dateObj || $dateObj->format('Y-m-d') !== $date) {
            throw new \InvalidArgumentException('Invalid date format. Expected Y-m-d');
        }

        // Validate that fromAccountId !== toAccountId (prevent self-transfers)
        if ($fromAccountId === $toAccountId) {
            throw new \InvalidArgumentException('Cannot transfer to the same account');
        }

        return DB::transaction(function () use ($fromAccountId, $toAccountId, $amount, $date, $userId, $description, $notes) {
            // Validate that both accounts exist and belong to the same user
            $fromAccount = $this->accountRepository->findById($fromAccountId);
            $toAccount = $this->accountRepository->findById($toAccountId);

            if (! $fromAccount || $fromAccount->user_id !== $userId) {
                throw new \InvalidArgumentException('Invalid source account');
            }

            if (! $toAccount || $toAccount->user_id !== $userId) {
                throw new \InvalidArgumentException('Invalid destination account');
            }

            // Create the outgoing transaction (from source account)
            $outgoingData = new TransactionData(
                account_id: $fromAccountId,
                type: 'transfer',
                amount: $amount,
                payee: 'Transfer',
                date: $date,
                category_id: null,
                description: $description,
                notes: $notes,
                related_transaction_id: null,
                user_id: $userId
            );

            $outgoingTransaction = $this->repository->create($outgoingData);

            // Create the incoming transaction (to destination account)
            $incomingData = new TransactionData(
                account_id: $toAccountId,
                type: 'income',
                amount: $amount,
                payee: 'Transfer',
                date: $date,
                category_id: null,
                description: $description,
                notes: $notes,
                related_transaction_id: $outgoingTransaction->id,
                user_id: $userId
            );

            $incomingTransaction = $this->repository->create($incomingData);

            // Update the outgoing transaction with the related transaction ID using repository
            $outgoingUpdateData = new TransactionData(
                account_id: $fromAccountId,
                type: 'transfer',
                amount: $amount,
                payee: 'Transfer',
                date: $date,
                category_id: null,
                description: $description,
                notes: $notes,
                related_transaction_id: $incomingTransaction->id,
                user_id: $userId
            );

            $outgoingTransaction = $this->repository->update($outgoingTransaction, $outgoingUpdateData);

            return [
                'outgoing' => $outgoingTransaction,
                'incoming' => $incomingTransaction,
            ];
        });
    }
}
