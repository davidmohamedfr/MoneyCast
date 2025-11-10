<?php

namespace App\Observers;

use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Data\TransactionData;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class AccountObserver
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Handle the Account "created" event.
     */
    public function created(Account $account): void
    {
        // Auto-create opening balance transaction if initial_balance is not zero
        if ($account->initial_balance != 0) {
            $type = $account->initial_balance >= 0 ? TransactionType::Income : TransactionType::Expense;
            $amount = abs((float) $account->initial_balance);

            $transactionData = new TransactionData(
                user_id: $account->user_id,
                account_id: $account->id,
                type: $type->value,
                amount: $amount,
                payee: 'Opening Balance',
                date: $account->created_at->format('Y-m-d'),
                description: 'Initial account balance',
                category_id: null,
                notes: null,
                related_transaction_id: null
            );

            $this->transactionRepository->create($transactionData);
        }
    }

    /**
     * Handle the Account "updated" event.
     */
    public function updated(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "restored" event.
     */
    public function restored(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "force deleted" event.
     */
    public function forceDeleted(Account $account): void
    {
        //
    }
}
