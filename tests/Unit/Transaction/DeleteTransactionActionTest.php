<?php

use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Actions\DeleteTransactionAction;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);
    $this->action = app(DeleteTransactionAction::class);
});

test('deletes simple income transaction', function () {
    $transaction = Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    $result = $this->action->execute($transaction);

    expect($result)->toBeTrue();
    assertDatabaseCount('transactions', 0);
});

test('deletes simple expense transaction', function () {
    $transaction = Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    $result = $this->action->execute($transaction);

    expect($result)->toBeTrue();
    assertDatabaseCount('transactions', 0);
});

test('deletes transfer transaction and its related transaction', function () {
    $secondAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    // Create outgoing transfer
    $outgoing = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => TransactionType::Transfer,
        'amount' => 500,
        'payee' => 'Transfer',
        'date' => now()->format('Y-m-d'),
    ]);

    // Create incoming transfer
    $incoming = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $secondAccount->id,
        'type' => TransactionType::Income,
        'amount' => 500,
        'payee' => 'Transfer',
        'date' => now()->format('Y-m-d'),
        'related_transaction_id' => $outgoing->id,
    ]);

    // Link them bidirectionally
    $outgoing->update(['related_transaction_id' => $incoming->id]);

    assertDatabaseCount('transactions', 2);

    // Delete the outgoing transfer
    $result = $this->action->execute($outgoing->fresh());

    expect($result)->toBeTrue();
    assertDatabaseCount('transactions', 0);
});

// Note: Testing non-existent related_transaction_id is difficult with FK constraints
// The action handles this case gracefully by checking if related transaction exists

test('does not delete other transactions when deleting a simple transaction', function () {
    $transaction1 = Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    $transaction2 = Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    $this->action->execute($transaction1);

    assertDatabaseCount('transactions', 1);
    assertDatabaseMissing('transactions', ['id' => $transaction1->id]);
});

test('handles transfer transaction with null related_transaction_id', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => TransactionType::Transfer,
        'amount' => 500,
        'payee' => 'Transfer',
        'date' => now()->format('Y-m-d'),
        'related_transaction_id' => null,
    ]);

    $result = $this->action->execute($transaction);

    expect($result)->toBeTrue();
    assertDatabaseCount('transactions', 0);
});

test('deletes only related transfer transactions, not unrelated ones', function () {
    $secondAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    // Unrelated transaction
    $unrelated = Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    // Transfer pair
    $outgoing = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => TransactionType::Transfer,
        'amount' => 500,
        'payee' => 'Transfer',
        'date' => now()->format('Y-m-d'),
    ]);

    $incoming = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $secondAccount->id,
        'type' => TransactionType::Income,
        'amount' => 500,
        'payee' => 'Transfer',
        'date' => now()->format('Y-m-d'),
        'related_transaction_id' => $outgoing->id,
    ]);

    $outgoing->update(['related_transaction_id' => $incoming->id]);

    assertDatabaseCount('transactions', 3);

    // Delete the transfer
    $this->action->execute($outgoing->fresh());

    // Should leave only the unrelated transaction
    assertDatabaseCount('transactions', 1);
    assertDatabaseMissing('transactions', ['id' => $outgoing->id]);
    assertDatabaseMissing('transactions', ['id' => $incoming->id]);
});

test('returns true on successful deletion', function () {
    $transaction = Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    $result = $this->action->execute($transaction);

    expect($result)->toBeTrue();
});

test('deletes opening balance transaction', function () {
    // Opening balance transactions are just regular income/expense transactions
    $openingBalance = Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'payee' => 'Opening Balance',
        'description' => 'Initial account balance',
    ]);

    $result = $this->action->execute($openingBalance);

    expect($result)->toBeTrue();
    assertDatabaseCount('transactions', 0);
});
