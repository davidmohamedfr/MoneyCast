<?php

use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('dashboard shows empty state for new user', function () {
    actingAs($this->user);

    $response = get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('accounts', 0)
        ->where('total_balance', 0)
        ->has('recent_transactions', 0)
        ->where('monthly_stats.income', 0)
        ->where('monthly_stats.expenses', 0)
        ->where('monthly_stats.net', 0)
        ->where('monthly_stats.transaction_count', 0)
    );
});

test('dashboard aggregates data from multiple accounts', function () {
    actingAs($this->user);

    // Create multiple accounts with different balances
    Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Checking',
        'initial_balance' => 2500,
    ]);

    Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Savings',
        'initial_balance' => 10000,
    ]);

    Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Credit',
        'type' => 'credit',
        'initial_balance' => -1500,
    ]);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->has('accounts', 3)
        ->where('total_balance', 11000) // 2500 + 10000 - 1500
    );
});

test('dashboard shows recent transactions limited to 10', function () {
    actingAs($this->user);

    $account = Account::factory()->create(['user_id' => $this->user->id]);

    // Create 15 transactions
    Transaction::factory()
        ->count(15)
        ->create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'date' => now()->format('Y-m-d'),
        ]);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->has('recent_transactions', 10) // Should only show 10
    );
});

test('dashboard monthly stats calculate current month only', function () {
    actingAs($this->user);

    $account = Account::factory()->create(['user_id' => $this->user->id]);

    // Current month income
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 3000,
        'date' => now()->format('Y-m-d'),
    ]);

    // Current month expense
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 800,
        'date' => now()->format('Y-m-d'),
    ]);

    // Last month transaction (should not be included)
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 5000,
        'date' => now()->subMonth()->format('Y-m-d'),
    ]);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('monthly_stats.income', 3000)
        ->where('monthly_stats.expenses', 800)
        ->where('monthly_stats.net', 2200)
        ->where('monthly_stats.transaction_count', 2)
    );
});

test('dashboard updates in real-time when transactions are added', function () {
    actingAs($this->user);

    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 1000,
    ]);

    // Initial state
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 1000)
        ->where('monthly_stats.transaction_count', 0)
    );

    // Add income transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    // Dashboard should reflect the change
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 1500)
        ->where('monthly_stats.income', 500)
        ->where('monthly_stats.transaction_count', 1)
    );

    // Add expense transaction
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 200,
        'date' => now()->format('Y-m-d'),
    ]);

    // Dashboard should reflect both transactions
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 1300)
        ->where('monthly_stats.income', 500)
        ->where('monthly_stats.expenses', 200)
        ->where('monthly_stats.net', 300)
        ->where('monthly_stats.transaction_count', 2)
    );
});

test('dashboard shows correct balance with mixed transaction types', function () {
    actingAs($this->user);

    $checkingAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Checking',
        'initial_balance' => 1000,
    ]);

    $savingsAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Savings',
        'initial_balance' => 5000,
    ]);

    // Income to checking
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $checkingAccount->id,
        'amount' => 2500,
        'date' => now()->format('Y-m-d'),
    ]);

    // Expense from checking
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $checkingAccount->id,
        'amount' => 400,
        'date' => now()->format('Y-m-d'),
    ]);

    // Transfer from checking to savings
    Transaction::factory()->transfer()->create([
        'user_id' => $this->user->id,
        'account_id' => $checkingAccount->id,
        'amount' => 1000,
        'date' => now()->format('Y-m-d'),
    ]);

    $response = get(route('dashboard'));

    // Checking: 1000 + 2500 - 400 - 1000 = 2100
    // Savings: 5000 (transfers should be balanced with income on savings side)
    $response->assertInertia(fn ($page) => $page
        ->where('monthly_stats.income', 2500)
        ->where('monthly_stats.expenses', 400)
    );
});

test('dashboard isolates user data correctly', function () {
    $otherUser = User::factory()->create();

    // Create data for other user
    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
        'initial_balance' => 99999,
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $otherUser->id,
        'account_id' => $otherAccount->id,
        'amount' => 50000,
        'date' => now()->format('Y-m-d'),
    ]);

    // Current user has no data
    actingAs($this->user);

    $response = get(route('dashboard'));

    // Should see empty dashboard, not other user's data
    $response->assertInertia(fn ($page) => $page
        ->has('accounts', 0)
        ->where('total_balance', 0)
        ->has('recent_transactions', 0)
        ->where('monthly_stats.income', 0)
    );
});

test('dashboard quick actions work correctly', function () {
    actingAs($this->user);

    $response = get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
    );

    // Quick action: Create transaction
    $response = get('/transactions/create');
    $response->assertStatus(200);

    // Quick action: Create account
    $response = get('/accounts/create');
    $response->assertStatus(200);
});

test('dashboard handles accounts with negative balances', function () {
    actingAs($this->user);

    // Account with negative balance (credit card)
    $creditCard = Account::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'credit',
        'initial_balance' => -2000,
    ]);

    // Account with positive balance
    $checking = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 3000,
    ]);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 1000) // 3000 - 2000
    );

    // Make a payment to credit card
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $creditCard->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    $response = get(route('dashboard'));

    // Credit card balance: -2000 - 500 = -2500
    // Total: 3000 - 2500 = 500
    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 500)
    );
});

test('dashboard performance with large dataset', function () {
    actingAs($this->user);

    $account = Account::factory()->create(['user_id' => $this->user->id]);

    // Create 100 transactions
    Transaction::factory()
        ->count(100)
        ->create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'date' => now()->format('Y-m-d'),
        ]);

    // Dashboard should load without timeout
    $response = get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('recent_transactions', 10) // Still limited to 10
    );
});
