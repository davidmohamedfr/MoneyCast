<?php

use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepository;
use App\Domain\Transaction\Services\TransactionService;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);
    $this->repository = new TransactionRepository;
    $this->service = new TransactionService($this->repository);
});

test('calculateAccountBalance correctly sums income transactions', function () {
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 1000,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    $balance = $this->service->calculateAccountBalance($this->account->id, 0);

    expect($balance)->toBe(1500.0);
});

test('calculateAccountBalance correctly subtracts expense transactions', function () {
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 300,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 150,
        'date' => now()->format('Y-m-d'),
    ]);

    $balance = $this->service->calculateAccountBalance($this->account->id, 0);

    expect($balance)->toBe(-450.0);
});

test('calculateAccountBalance handles mixed transaction types', function () {
    // Initial: 0
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 2000,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->transfer()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 300,
        'date' => now()->format('Y-m-d'),
    ]);

    // 0 + 2000 - 500 - 300 = 1200
    $balance = $this->service->calculateAccountBalance($this->account->id, 0);

    expect($balance)->toBe(1200.0);
});

test('calculateAccountBalance respects upToDate parameter', function () {
    // Past transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 1000,
        'date' => now()->subDays(5)->format('Y-m-d'),
    ]);

    // Current transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    // Future transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 300,
        'date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    // Calculate up to today (should include past + current only)
    $balance = $this->service->calculateAccountBalance(
        $this->account->id,
        0,
        now()->format('Y-m-d')
    );

    expect($balance)->toBe(1500.0);
});

test('calculateAccountBalance includes all transactions when no date limit', function () {
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 1000,
        'date' => now()->subDays(5)->format('Y-m-d'),
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 300,
        'date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    // No date limit - should include all
    $balance = $this->service->calculateAccountBalance($this->account->id, 0);

    expect($balance)->toBe(1800.0);
});

test('calculateAccountBalance uses initial balance as starting point', function () {
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    // Start with initial balance of 1000
    $balance = $this->service->calculateAccountBalance($this->account->id, 1000);

    expect($balance)->toBe(1500.0);
});

test('calculateAccountStats correctly aggregates income and expenses', function () {
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 3000,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 1500,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 800,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 400,
        'date' => now()->format('Y-m-d'),
    ]);

    $stats = $this->service->calculateAccountStats($this->account->id);

    expect($stats['total_income'])->toBe(4500.0)
        ->and($stats['total_expenses'])->toBe(1200.0);
});

test('calculateAccountStats ignores transfer transactions', function () {
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 1000,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->transfer()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    $stats = $this->service->calculateAccountStats($this->account->id);

    expect($stats['total_income'])->toBe(1000.0)
        ->and($stats['total_expenses'])->toBe(0.0);
});

test('calculateAccountBalance rounds to 4 decimal places', function () {
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 10.555,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 10.444,
        'date' => now()->format('Y-m-d'),
    ]);

    $balance = $this->service->calculateAccountBalance($this->account->id, 0);

    expect($balance)->toBe(20.999);
});

test('calculateAccountBalance handles negative balances correctly', function () {
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 1500,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    $balance = $this->service->calculateAccountBalance($this->account->id, 0);

    expect($balance)->toBe(-1000.0);
});
