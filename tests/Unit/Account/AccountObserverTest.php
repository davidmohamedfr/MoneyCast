<?php

use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;

test('observer creates opening balance transaction for positive initial balance', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 1000,
        'currency' => 'EUR',
    ]);

    // Should create one opening balance transaction
    $openingTransaction = Transaction::where('account_id', $account->id)
        ->where('payee', 'Opening Balance')
        ->first();

    expect($openingTransaction)->not->toBeNull()
        ->and($openingTransaction->type)->toBe(TransactionType::Income)
        ->and($openingTransaction->amount)->toBe('1000.0000')
        ->and($openingTransaction->description)->toBe('Initial account balance')
        ->and($openingTransaction->user_id)->toBe($user->id);
});

test('observer creates opening balance transaction for negative initial balance', function () {
    $user = User::factory()->create();

    // Create credit card account with negative balance
    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Credit Card',
        'type' => 'credit',
        'bank' => 'Test Bank',
        'initial_balance' => -500,
        'currency' => 'EUR',
    ]);

    // Should create expense transaction for negative balance
    $openingTransaction = Transaction::where('account_id', $account->id)
        ->where('payee', 'Opening Balance')
        ->first();

    expect($openingTransaction)->not->toBeNull()
        ->and($openingTransaction->type)->toBe(TransactionType::Expense)
        ->and($openingTransaction->amount)->toBe('500.0000') // Absolute value
        ->and($openingTransaction->description)->toBe('Initial account balance');
});

test('observer does not create opening balance transaction for zero initial balance', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Empty Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 0,
        'currency' => 'EUR',
    ]);

    // Should not create any transaction
    $transactionCount = Transaction::where('account_id', $account->id)->count();

    expect($transactionCount)->toBe(0);
});

test('observer uses account created_at date for opening balance transaction', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'type' => 'savings',
        'bank' => 'Test Bank',
        'initial_balance' => 2500,
        'currency' => 'EUR',
    ]);

    $openingTransaction = Transaction::where('account_id', $account->id)
        ->where('payee', 'Opening Balance')
        ->first();

    // Transaction date field is cast to Carbon, so compare formatted dates
    expect($openingTransaction->date->format('Y-m-d'))
        ->toBe($account->created_at->format('Y-m-d'));
});

test('observer creates opening balance transaction only once during account creation', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 750,
        'currency' => 'EUR',
    ]);

    // Update account (should not create another opening balance)
    $account->update(['name' => 'Updated Account']);

    $openingTransactionCount = Transaction::where('account_id', $account->id)
        ->where('payee', 'Opening Balance')
        ->count();

    expect($openingTransactionCount)->toBe(1);
});

test('observer handles very large initial balance', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Rich Account',
        'type' => 'savings',
        'bank' => 'Test Bank',
        'initial_balance' => 999999.99,
        'currency' => 'EUR',
    ]);

    $openingTransaction = Transaction::where('account_id', $account->id)
        ->where('payee', 'Opening Balance')
        ->first();

    expect($openingTransaction)->not->toBeNull()
        ->and($openingTransaction->amount)->toBe('999999.9900');
});

test('observer handles fractional initial balance', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Precise Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 123.45,
        'currency' => 'EUR',
    ]);

    $openingTransaction = Transaction::where('account_id', $account->id)
        ->where('payee', 'Opening Balance')
        ->first();

    expect($openingTransaction->amount)->toBe('123.4500');
});
