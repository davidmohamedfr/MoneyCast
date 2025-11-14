<?php

use App\Domain\Account\Models\Account;
use App\Domain\Dashboard\Services\DashboardService;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    seedCategories();
    $this->user = User::factory()->create();
});

test('getDashboardData returns correct structure', function () {
    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    expect($data)->toHaveKeys([
        'accounts',
        'total_balance',
        'recent_transactions',
        'monthly_stats',
    ])
        ->and($data['accounts'])->toBeArray()
        ->and($data['total_balance'])->toBeFloat()
        ->and($data['recent_transactions'])->toBeArray()
        ->and($data['monthly_stats'])->toHaveKeys([
            'income',
            'expenses',
            'net',
            'transaction_count',
        ]);
});

test('getDashboardData calculates total balance correctly', function () {
    // Account 1: 1000
    Account::create([
        'user_id' => $this->user->id,
        'name' => 'Checking',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 1000,
        'currency' => 'EUR',
    ]);

    // Account 2: 500
    Account::create([
        'user_id' => $this->user->id,
        'name' => 'Savings',
        'type' => 'savings',
        'bank' => 'Test Bank',
        'initial_balance' => 500,
        'currency' => 'EUR',
    ]);

    // Account 3: -200 (credit card)
    Account::create([
        'user_id' => $this->user->id,
        'name' => 'Credit Card',
        'type' => 'credit',
        'bank' => 'Test Bank',
        'initial_balance' => -200,
        'currency' => 'EUR',
    ]);

    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    // 1000 + 500 - 200 = 1300
    expect($data['total_balance'])->toBe(1300.0);
});

test('getDashboardData excludes opening balance from monthly stats', function () {
    $account = Account::create([
        'user_id' => $this->user->id,
        'name' => 'Test Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 5000, // Creates opening balance transaction
        'currency' => 'EUR',
    ]);

    // User transaction this month
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 300,
        'date' => now()->format('Y-m-d'),
    ]);

    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    // Monthly stats should not include the 5000 opening balance
    expect($data['monthly_stats']['income'])->toBe(0.0)
        ->and($data['monthly_stats']['expenses'])->toBe(300.0)
        ->and($data['monthly_stats']['net'])->toBe(-300.0)
        ->and($data['monthly_stats']['transaction_count'])->toBe(1);
});

test('getDashboardData only includes current month in monthly stats', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    // Last month transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 2000,
        'date' => now()->subMonth()->format('Y-m-d'),
    ]);

    // This month transactions
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 1500,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    // Next month transaction
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 300,
        'date' => now()->addMonth()->format('Y-m-d'),
    ]);

    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    // Should only include this month's transactions
    expect($data['monthly_stats']['income'])->toBe(1500.0)
        ->and($data['monthly_stats']['expenses'])->toBe(500.0)
        ->and($data['monthly_stats']['net'])->toBe(1000.0)
        ->and($data['monthly_stats']['transaction_count'])->toBe(2);
});

test('getDashboardData limits recent transactions to 10', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    // Create 15 transactions
    for ($i = 0; $i < 15; $i++) {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'date' => now()->subDays($i)->format('Y-m-d'),
        ]);
    }

    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    expect($data['recent_transactions'])->toHaveCount(10);
});

test('getDashboardData orders recent transactions by date descending', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 100,
        'date' => now()->subDays(5)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 200,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 300,
        'date' => now()->subDays(2)->format('Y-m-d'),
    ]);

    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    // toArray() converts to numerical array, access by array index
    $transactions = array_values($data['recent_transactions']);

    // First should be most recent (200), then 300, then 100
    expect($transactions[0]['amount'])->toBe('200.0000')
        ->and($transactions[1]['amount'])->toBe('300.0000')
        ->and($transactions[2]['amount'])->toBe('100.0000');
});

test('getDashboardData excludes archived accounts', function () {
    $activeAccount = Account::create([
        'user_id' => $this->user->id,
        'name' => 'Active Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 1000,
        'currency' => 'EUR',
    ]);

    $archivedAccount = Account::create([
        'user_id' => $this->user->id,
        'name' => 'Archived Account',
        'type' => 'savings',
        'bank' => 'Test Bank',
        'initial_balance' => 500,
        'currency' => 'EUR',
    ]);

    $archivedAccount->delete(); // Soft delete

    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    expect($data['accounts'])->toHaveCount(1)
        ->and($data['accounts'][0]['account']->name)->toBe('Active Account')
        // Total balance should only include active account
        ->and($data['total_balance'])->toBe(1000.0);
});

test('getDashboardData handles user with no accounts', function () {
    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    expect($data['accounts'])->toHaveCount(0)
        ->and($data['total_balance'])->toBe(0.0)
        ->and($data['recent_transactions'])->toHaveCount(0)
        ->and($data['monthly_stats']['income'])->toBe(0.0)
        ->and($data['monthly_stats']['expenses'])->toBe(0.0)
        ->and($data['monthly_stats']['net'])->toBe(0.0)
        ->and($data['monthly_stats']['transaction_count'])->toBe(0);
});

test('getDashboardData isolates user data correctly', function () {
    $otherUser = User::factory()->create();

    // Create accounts for both users
    Account::create([
        'user_id' => $this->user->id,
        'name' => 'My Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 1000,
        'currency' => 'EUR',
    ]);

    Account::create([
        'user_id' => $otherUser->id,
        'name' => 'Other Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 5000,
        'currency' => 'EUR',
    ]);

    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    expect($data['accounts'])->toHaveCount(1)
        ->and($data['accounts'][0]['account']->name)->toBe('My Account')
        ->and($data['total_balance'])->toBe(1000.0);
});

test('getDashboardData calculates net correctly with mixed transactions', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 0,
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 3000,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 1200,
        'date' => now()->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    $service = app(DashboardService::class);
    $data = $service->getDashboardData($this->user->id);

    // Net = 3000 - 1200 - 500 = 1300
    expect($data['monthly_stats']['net'])->toBe(1300.0);
});
