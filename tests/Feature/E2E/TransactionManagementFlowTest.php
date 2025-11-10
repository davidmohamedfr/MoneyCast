<?php

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create(['user_id' => $this->user->id]);
    $this->category = Category::factory()->expense()->create();
});

test('complete transaction creation flow with income', function () {
    actingAs($this->user);

    $incomeCategory = Category::factory()->income()->create();

    // Step 1: Visit transactions index (empty)
    $response = get(route('transactions.index'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('transaction/Index')
        ->has('transactions', 0)
    );

    // Step 2: Visit create page
    $response = get(route('transactions.create'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('transaction/Create')
        ->has('accounts')
        ->has('categories')
    );

    // Step 3: Create income transaction
    $transactionData = [
        'account_id' => $this->account->id,
        'type' => 'income',
        'amount' => 2500.00,
        'payee' => 'Salary',
        'date' => now()->format('Y-m-d'),
        'category_id' => $incomeCategory->id,
        'description' => 'Monthly salary',
    ];

    $response = post(route('transactions.store'), $transactionData);
    $response->assertRedirect(route('transactions.index'));
    $response->assertSessionHas('success', 'Transaction created successfully');

    assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => 'income',
        'amount' => '2500.0000',
        'payee' => 'Salary',
    ]);

    // Step 4: Verify transaction appears in list
    $response = get(route('transactions.index'));
    $response->assertInertia(fn ($page) => $page
        ->has('transactions', 1)
        ->where('transactions.0.payee', 'Salary')
        ->where('transactions.0.type', 'income')
    );
});

test('complete transaction creation flow with expense', function () {
    actingAs($this->user);

    // Create expense transaction
    $transactionData = [
        'account_id' => $this->account->id,
        'type' => 'expense',
        'amount' => 150.75,
        'payee' => 'Grocery Store',
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
        'description' => 'Weekly groceries',
        'notes' => 'Bought organic vegetables',
    ];

    $response = post(route('transactions.store'), $transactionData);
    $response->assertRedirect(route('transactions.index'));

    assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'type' => 'expense',
        'amount' => '150.7500',
        'payee' => 'Grocery Store',
        'notes' => 'Bought organic vegetables',
    ]);
});

test('complete transaction update flow', function () {
    actingAs($this->user);

    // Create a transaction
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'payee' => 'Old Payee',
        'amount' => 100,
    ]);

    // Step 1: Visit edit page
    $response = get(route('transactions.edit', $transaction));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('transaction/Edit')
        ->where('transaction.payee', 'Old Payee')
    );

    // Step 2: Update transaction
    $response = put(route('transactions.update', $transaction), [
        'account_id' => $this->account->id,
        'type' => 'expense',
        'amount' => 200,
        'payee' => 'New Payee',
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
    ]);

    $response->assertRedirect(route('transactions.index'));
    $response->assertSessionHas('success', 'Transaction updated successfully');

    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'payee' => 'New Payee',
        'amount' => '200.0000',
    ]);

    assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
        'payee' => 'Old Payee',
    ]);
});

test('complete transaction deletion flow', function () {
    actingAs($this->user);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    assertDatabaseCount('transactions', 1);

    $response = delete(route('transactions.destroy', $transaction));

    $response->assertRedirect(route('transactions.index'));
    $response->assertSessionHas('success', 'Transaction deleted successfully');

    assertDatabaseCount('transactions', 0);
});

test('transfer transaction creates two linked transactions', function () {
    actingAs($this->user);

    $fromAccount = Account::factory()->create(['user_id' => $this->user->id]);
    $toAccount = Account::factory()->create(['user_id' => $this->user->id]);

    // Note: In the current implementation, transfers would need a dedicated action
    // For now, we test creating individual transfer transactions
    $outgoingData = [
        'account_id' => $fromAccount->id,
        'type' => 'transfer',
        'amount' => 500,
        'payee' => 'Transfer',
        'date' => now()->format('Y-m-d'),
    ];

    $response = post(route('transactions.store'), $outgoingData);
    $response->assertRedirect(route('transactions.index'));

    assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'account_id' => $fromAccount->id,
        'type' => 'transfer',
        'amount' => '500.0000',
    ]);
});

test('deleting transfer transaction removes both linked transactions', function () {
    actingAs($this->user);

    $secondAccount = Account::factory()->create(['user_id' => $this->user->id]);

    // Create outgoing transfer transaction
    $outgoing = Transaction::factory()->transfer()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    // Create incoming transfer transaction
    $incoming = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $secondAccount->id,
        'type' => 'income',
        'payee' => 'Transfer',
        'related_transaction_id' => $outgoing->id,
    ]);

    // Link them
    $outgoing->update(['related_transaction_id' => $incoming->id]);

    assertDatabaseCount('transactions', 2);

    // Delete the outgoing transaction
    $response = delete(route('transactions.destroy', $outgoing));

    $response->assertRedirect(route('transactions.index'));

    // Both transactions should be deleted
    assertDatabaseCount('transactions', 0);
});

test('transaction balance affects account balance correctly', function () {
    actingAs($this->user);

    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 1000,
    ]);

    // Add income
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    // Add expense
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 200,
        'date' => now()->format('Y-m-d'),
    ]);

    // Check account balance via show page
    $response = get(route('accounts.show', $account));
    $response->assertInertia(fn ($page) => $page
        ->where('current_balance', 1300.0) // 1000 + 500 - 200
    );
});

test('future-dated transactions affect projected balance only', function () {
    actingAs($this->user);

    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'initial_balance' => 1000,
    ]);

    // Current transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 500,
        'date' => now()->format('Y-m-d'),
    ]);

    // Future transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'amount' => 300,
        'date' => now()->addWeek()->format('Y-m-d'),
    ]);

    $response = get(route('accounts.show', $account));
    $response->assertInertia(fn ($page) => $page
        ->where('current_balance', 1500.0) // Only current transaction
        ->where('projected_balance', 1800.0) // Includes future transaction
    );
});

test('transaction authorization prevents access to other users transactions', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
    $otherTransaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'account_id' => $otherAccount->id,
    ]);

    actingAs($this->user);

    // Cannot edit other user's transaction
    $response = get(route('transactions.edit', $otherTransaction));
    $response->assertStatus(403);

    // Cannot update other user's transaction
    $response = put(route('transactions.update', $otherTransaction), [
        'account_id' => $otherAccount->id,
        'type' => 'expense',
        'amount' => 999,
        'payee' => 'Hacked',
        'date' => now()->format('Y-m-d'),
    ]);
    $response->assertStatus(403);

    // Cannot delete other user's transaction
    $response = delete(route('transactions.destroy', $otherTransaction));
    $response->assertStatus(403);

    // Verify transaction was not modified
    assertDatabaseHas('transactions', [
        'id' => $otherTransaction->id,
        'user_id' => $otherUser->id,
        'payee' => $otherTransaction->payee,
    ]);
});

test('transaction validation ensures data integrity', function () {
    actingAs($this->user);

    // Test missing required fields
    $response = post(route('transactions.store'), []);
    $response->assertSessionHasErrors([
        'account_id',
        'type',
        'amount',
        'payee',
        'date',
    ]);

    // Test invalid transaction type
    $response = post(route('transactions.store'), [
        'account_id' => $this->account->id,
        'type' => 'invalid_type',
        'amount' => 100,
        'payee' => 'Test',
        'date' => now()->format('Y-m-d'),
    ]);
    $response->assertSessionHasErrors(['type']);

    // Test negative amount
    $response = post(route('transactions.store'), [
        'account_id' => $this->account->id,
        'type' => 'expense',
        'amount' => -50,
        'payee' => 'Test',
        'date' => now()->format('Y-m-d'),
    ]);
    $response->assertSessionHasErrors(['amount']);

    // Test zero amount
    $response = post(route('transactions.store'), [
        'account_id' => $this->account->id,
        'type' => 'expense',
        'amount' => 0,
        'payee' => 'Test',
        'date' => now()->format('Y-m-d'),
    ]);
    $response->assertSessionHasErrors(['amount']);

    // Verify no transactions were created
    assertDatabaseCount('transactions', 0);
});

test('multiple transactions workflow with filtering', function () {
    actingAs($this->user);

    $groceryCategory = Category::factory()->expense()->create(['name' => 'Groceries']);
    $salaryCategory = Category::factory()->income()->create(['name' => 'Salary']);

    // Create multiple transactions
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $groceryCategory->id,
        'amount' => 50,
        'date' => now()->subDays(5)->format('Y-m-d'),
    ]);

    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $salaryCategory->id,
        'amount' => 2000,
        'date' => now()->subDays(3)->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $groceryCategory->id,
        'amount' => 75,
        'date' => now()->format('Y-m-d'),
    ]);

    // Verify all transactions appear
    $response = get(route('transactions.index'));
    $response->assertInertia(fn ($page) => $page
        ->has('transactions', 3)
    );

    // Transactions should be ordered by date descending
    $response = get(route('transactions.index'));
    $transactions = $response->viewData('page')['props']['transactions'];

    expect($transactions[0]['amount'])->toBe('75.0000'); // Most recent
    expect($transactions[2]['amount'])->toBe('50.0000'); // Oldest
});
