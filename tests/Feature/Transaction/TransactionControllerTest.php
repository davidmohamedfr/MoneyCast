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
    $this->category = Category::factory()->create(['type' => 'expense']);
});

test('index displays transactions for authenticated user', function () {
    actingAs($this->user);

    Transaction::factory()
        ->count(3)
        ->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
        ]);

    $response = get(route('transactions.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('transaction/Index')
        ->has('transactions', 3)
    );
});

test('create displays transaction form', function () {
    actingAs($this->user);

    $response = get(route('transactions.create'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('transaction/Create')
        ->has('accounts')
        ->has('categories')
    );
});

test('store creates a new transaction', function () {
    actingAs($this->user);

    $data = [
        'account_id' => $this->account->id,
        'type' => 'expense',
        'amount' => 50.00,
        'payee' => 'Grocery Store',
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
        'description' => 'Weekly groceries',
    ];

    $response = post(route('transactions.store'), $data);

    $response->assertRedirect(route('transactions.index'));
    $response->assertSessionHas('success', 'Transaction created successfully');

    assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => 'expense',
        'amount' => '50.0000',
        'payee' => 'Grocery Store',
    ]);
});

test('store validates required fields', function () {
    actingAs($this->user);

    $response = post(route('transactions.store'), []);

    $response->assertSessionHasErrors([
        'account_id',
        'type',
        'amount',
        'payee',
        'date',
    ]);
});

test('edit displays transaction edit form', function () {
    actingAs($this->user);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    $response = get(route('transactions.edit', $transaction));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('transaction/Edit')
        ->has('transaction')
        ->has('accounts')
        ->has('categories')
    );
});

test('update modifies existing transaction', function () {
    actingAs($this->user);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'payee' => 'Old Payee',
    ]);

    $data = [
        'account_id' => $this->account->id,
        'type' => 'expense',
        'amount' => 75.00,
        'payee' => 'New Payee',
        'date' => now()->format('Y-m-d'),
    ];

    $response = put(route('transactions.update', $transaction), $data);

    $response->assertRedirect(route('transactions.index'));
    $response->assertSessionHas('success', 'Transaction updated successfully');

    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'payee' => 'New Payee',
        'amount' => '75.0000',
    ]);

    assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
        'payee' => 'Old Payee',
    ]);
});

test('destroy deletes transaction', function () {
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

test('destroy deletes related transaction for transfers', function () {
    actingAs($this->user);

    $secondAccount = Account::factory()->create(['user_id' => $this->user->id]);

    // Create transfer with related transaction
    $outgoing = Transaction::factory()->transfer()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);

    $incoming = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $secondAccount->id,
        'type' => 'income',
        'payee' => 'Transfer',
        'related_transaction_id' => $outgoing->id,
    ]);

    $outgoing->update(['related_transaction_id' => $incoming->id]);

    assertDatabaseCount('transactions', 2);

    $response = delete(route('transactions.destroy', $outgoing));

    $response->assertRedirect(route('transactions.index'));

    assertDatabaseCount('transactions', 0);
});

test('user cannot view other users transactions', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
    $transaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'account_id' => $otherAccount->id,
    ]);

    actingAs($this->user);

    $response = get(route('transactions.edit', $transaction));

    $response->assertStatus(403);
});

test('user cannot update other users transactions', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
    $transaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'account_id' => $otherAccount->id,
    ]);

    actingAs($this->user);

    $response = put(route('transactions.update', $transaction), [
        'account_id' => $otherAccount->id,
        'type' => 'expense',
        'amount' => 100,
        'payee' => 'Test',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertStatus(403);
});

test('user cannot delete other users transactions', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);
    $transaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'account_id' => $otherAccount->id,
    ]);

    actingAs($this->user);

    $response = delete(route('transactions.destroy', $transaction));

    $response->assertStatus(403);
});
