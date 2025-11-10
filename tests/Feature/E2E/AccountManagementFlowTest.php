<?php

use App\Domain\Account\Models\Account;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('complete account creation flow', function () {
    actingAs($this->user);

    // Step 1: User visits accounts index and sees empty state
    $response = get(route('accounts.index'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('account/Index')
        ->has('accounts', 0)
    );

    // Step 2: User clicks create account
    $response = get(route('accounts.create'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('account/Create')
    );

    // Step 3: User submits account creation form
    $accountData = [
        'name' => 'My Checking Account',
        'type' => 'checking',
        'initial_balance' => 1000.50,
        'currency' => 'EUR',
    ];

    $response = post(route('accounts.store'), $accountData);
    $response->assertRedirect(route('accounts.index'));
    $response->assertSessionHas('success', 'Account created successfully');

    assertDatabaseHas('accounts', [
        'user_id' => $this->user->id,
        'name' => 'My Checking Account',
        'type' => 'checking',
        'initial_balance' => '1000.5000',
        'currency' => 'EUR',
    ]);

    // Step 4: User sees the account in the list
    $response = get(route('accounts.index'));
    $response->assertInertia(fn ($page) => $page
        ->has('accounts', 1)
        ->where('accounts.0.account.name', 'My Checking Account')
        ->where('accounts.0.current_balance', 1000.50)
    );
});

test('complete account update flow', function () {
    actingAs($this->user);

    // Create an account
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Old Name',
        'type' => 'checking',
    ]);

    // Step 1: User visits account show page
    $response = get(route('accounts.show', $account));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('account/Show')
        ->where('account.name', 'Old Name')
    );

    // Step 2: User clicks edit
    $response = get(route('accounts.edit', $account));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('account/Edit')
        ->where('account.name', 'Old Name')
    );

    // Step 3: User updates the account
    $response = put(route('accounts.update', $account), [
        'name' => 'New Name',
        'type' => 'savings',
    ]);

    $response->assertRedirect(route('accounts.index'));
    $response->assertSessionHas('success', 'Account updated successfully');

    assertDatabaseHas('accounts', [
        'id' => $account->id,
        'name' => 'New Name',
        'type' => 'savings',
    ]);

    assertDatabaseMissing('accounts', [
        'id' => $account->id,
        'name' => 'Old Name',
    ]);
});

test('complete account deletion flow', function () {
    actingAs($this->user);

    // Create an account without transactions
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Account to Delete',
    ]);

    assertDatabaseCount('accounts', 1);

    // User deletes the account
    $response = delete(route('accounts.destroy', $account));

    $response->assertRedirect(route('accounts.index'));
    $response->assertSessionHas('success', 'Account deleted successfully');

    assertDatabaseCount('accounts', 0);
});

test('account deletion fails when transactions exist', function () {
    actingAs($this->user);

    $account = Account::factory()->create(['user_id' => $this->user->id]);

    // Create a transaction for this account
    $account->transactions()->create([
        'user_id' => $this->user->id,
        'type' => 'income',
        'amount' => 100,
        'payee' => 'Test Payee',
        'date' => now()->format('Y-m-d'),
    ]);

    $response = delete(route('accounts.destroy', $account));

    $response->assertRedirect(route('accounts.index'));
    $response->assertSessionHas('error', 'Cannot delete account with transactions');

    // Account should still exist
    assertDatabaseHas('accounts', [
        'id' => $account->id,
    ]);
});

test('complete multi-account workflow', function () {
    actingAs($this->user);

    // Step 1: Create multiple accounts
    $checking = Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Checking',
        'initial_balance' => 1000,
    ]);

    $savings = Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Savings',
        'initial_balance' => 5000,
    ]);

    $credit = Account::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Credit Card',
        'type' => 'credit',
        'initial_balance' => -500,
    ]);

    // Step 2: Verify all accounts appear in index
    $response = get(route('accounts.index'));
    $response->assertInertia(fn ($page) => $page
        ->has('accounts', 3)
    );

    // Step 3: Verify total balances are calculated correctly
    $response = get(route('accounts.index'));
    $response->assertStatus(200);

    $accounts = $response->viewData('page')['props']['accounts'];
    $totalBalance = array_reduce($accounts, fn($sum, $acc) => $sum + $acc['current_balance'], 0);

    expect($totalBalance)->toBe(5500.0); // 1000 + 5000 - 500
});

test('account authorization prevents access to other users accounts', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);

    actingAs($this->user);

    // Cannot view other user's account
    $response = get(route('accounts.show', $otherAccount));
    $response->assertStatus(403);

    // Cannot edit other user's account
    $response = get(route('accounts.edit', $otherAccount));
    $response->assertStatus(403);

    // Cannot update other user's account
    $response = put(route('accounts.update', $otherAccount), [
        'name' => 'Hacked Name',
        'type' => 'checking',
    ]);
    $response->assertStatus(403);

    // Cannot delete other user's account
    $response = delete(route('accounts.destroy', $otherAccount));
    $response->assertStatus(403);

    // Verify account was not modified
    assertDatabaseHas('accounts', [
        'id' => $otherAccount->id,
        'user_id' => $otherUser->id,
        'name' => $otherAccount->name,
    ]);
});

test('account validation ensures data integrity', function () {
    actingAs($this->user);

    // Test missing required fields
    $response = post(route('accounts.store'), []);
    $response->assertSessionHasErrors(['name', 'type', 'initial_balance']);

    // Test invalid account type
    $response = post(route('accounts.store'), [
        'name' => 'Test Account',
        'type' => 'invalid_type',
        'initial_balance' => 1000,
        'currency' => 'EUR',
    ]);
    $response->assertSessionHasErrors(['type']);

    // Test negative initial balance
    $response = post(route('accounts.store'), [
        'name' => 'Test Account',
        'type' => 'checking',
        'initial_balance' => -100,
        'currency' => 'EUR',
    ]);
    $response->assertSessionHasErrors(['initial_balance']);

    // Test invalid currency code
    $response = post(route('accounts.store'), [
        'name' => 'Test Account',
        'type' => 'checking',
        'initial_balance' => 1000,
        'currency' => 'INVALID',
    ]);
    $response->assertSessionHasErrors(['currency']);

    // Verify no accounts were created
    assertDatabaseCount('accounts', 0);
});
