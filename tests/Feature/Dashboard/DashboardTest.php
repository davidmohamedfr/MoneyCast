<?php

use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('dashboard displays correctly for authenticated user', function () {
    actingAs($this->user);

    $response = get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('accounts')
        ->has('total_balance')
        ->has('recent_transactions')
        ->has('monthly_stats')
    );
});

test('dashboard shows correct total balance', function () {
    actingAs($this->user);

    Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 1000,
    ]);

    Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 500,
    ]);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 1500)
    );
});

test('dashboard shows recent transactions', function () {
    actingAs($this->user);

    $account = Account::factory()->create(['user_id' => $this->user->id]);

    Transaction::factory()
        ->count(15)
        ->create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
        ]);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->has('recent_transactions', 10) // Should show only 10 most recent
    );
});

test('dashboard shows monthly stats', function () {
    actingAs($this->user);

    $account = Account::factory()->create(['user_id' => $this->user->id]);

    // Create income transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 1000,
        'date' => now()->format('Y-m-d'),
    ]);

    // Create expense transaction
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 300,
        'date' => now()->format('Y-m-d'),
    ]);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('monthly_stats.income', 1000)
        ->where('monthly_stats.expenses', 300)
        ->where('monthly_stats.net', 700)
        ->where('monthly_stats.transaction_count', 2)
    );
});

test('dashboard excludes other users data', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);

    Transaction::factory()
        ->count(5)
        ->create([
            'user_id' => $otherUser->id,
            'account_id' => $otherAccount->id,
        ]);

    actingAs($this->user);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->has('recent_transactions', 0)
        ->where('total_balance', 0)
    );
});

test('dashboard monthly stats only includes current month transactions', function () {
    actingAs($this->user);

    $account = Account::factory()->create(['user_id' => $this->user->id]);

    // Current month transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    // Last month transaction - should not be included
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 200,
        'date' => now()->subMonth()->format('Y-m-d'),
    ]);

    $response = get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('monthly_stats.income', 500)
        ->where('monthly_stats.expenses', 0)
        ->where('monthly_stats.transaction_count', 1)
    );
});

test('dashboard redirects unauthenticated users to login', function () {
    $response = get(route('dashboard'));

    $response->assertRedirect(route('login'));
});
