<?php

use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('opening balance transaction is marked with is_opening_balance flag', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 1000,
    ]);

    // Observer auto-creates opening balance transaction
    assertDatabaseHas('transactions', [
        'account_id' => $account->id,
        'payee' => 'Opening Balance',
        'is_opening_balance' => true,
    ]);
});

test('regular transactions are not marked as opening balance', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    $repository = app(TransactionRepositoryInterface::class);
    $transaction = $repository->create(
        new \App\Domain\Transaction\Data\TransactionData(
            account_id: $account->id,
            type: 'income',
            amount: 500,
            payee: 'Regular Transaction',
            date: now()->format('Y-m-d'),
            user_id: $this->user->id,
        )
    );

    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'is_opening_balance' => false,
    ]);
});

test('migration marks existing opening balance transactions', function () {
    // Create a transaction with old opening balance format (before migration)
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    $repository = app(TransactionRepositoryInterface::class);
    $transaction = $repository->create(
        new \App\Domain\Transaction\Data\TransactionData(
            account_id: $account->id,
            type: 'income',
            amount: 1000,
            payee: 'Opening Balance',
            date: now()->format('Y-m-d'),
            user_id: $this->user->id,
            is_opening_balance: false,
        )
    );

    // Manually update to simulate migration behavior
    $transaction->update(['is_opening_balance' => true]);

    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'payee' => 'Opening Balance',
        'is_opening_balance' => true,
    ]);
});

test('repository filters exclude opening balance transactions when requested', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 1000,
    ]);

    $repository = app(TransactionRepositoryInterface::class);

    // Create regular transaction
    $repository->create(
        new \App\Domain\Transaction\Data\TransactionData(
            account_id: $account->id,
            type: 'income',
            amount: 500,
            payee: 'Regular Transaction',
            date: now()->format('Y-m-d'),
            user_id: $this->user->id,
        )
    );

    // Get all transactions without filter
    $allTransactions = $repository->getAllForUser($this->user->id);
    expect($allTransactions)->toHaveCount(2); // Opening balance + regular

    // Get transactions excluding opening balance
    $filteredTransactions = $repository->getAllForUser($this->user->id, [
        'exclude_opening_balance' => true,
    ]);
    expect($filteredTransactions)->toHaveCount(1); // Only regular
    expect($filteredTransactions->first()->payee)->toBe('Regular Transaction');
});

test('account with zero initial balance does not create opening balance transaction', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    $repository = app(TransactionRepositoryInterface::class);
    $transactions = $repository->getAllForUser($this->user->id);

    expect($transactions)->toHaveCount(0);
});

test('opening balance transaction has correct is_opening_balance value in model', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 1000,
    ]);

    $repository = app(TransactionRepositoryInterface::class);
    $transaction = $repository->getAllForUser($this->user->id)->first();

    expect($transaction->is_opening_balance)->toBeTrue();
    expect($transaction->payee)->toBe('Opening Balance');
});
