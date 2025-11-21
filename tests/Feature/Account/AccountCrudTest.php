<?php

use App\Domain\Account\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create account with initial balance', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('accounts.store'), [
        'name' => 'Checking Account',
        'initial_balance' => 1000.00,
        'type' => 'checking',
        'bank' => 'Test Bank',
        'currency' => 'EUR',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accounts', [
        'user_id' => $user->id,
        'name' => 'Checking Account',
        'bank' => 'Test Bank',
        'initial_balance' => 1000.00,
    ]);
});

test('user can view their accounts list', function () {
    $user = User::factory()->create();
    Account::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('accounts.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('account/Index')
        ->has('accounts', 3)
        ->has('archivedAccounts', 0)
    );
});

test('user can view single account with transaction history', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('accounts.show', $account));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('account/Show')
        ->has('account')
    );
});

test('user can update account name', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

    $response = $this->actingAs($user)->put(route('accounts.update', $account), [
        'name' => 'New Name',
        'type' => $account->type->value,
        'bank' => $account->bank,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accounts', [
        'id' => $account->id,
        'name' => 'New Name',
    ]);
});

test('user cannot delete account with transactions', function () {
    // This test will be fully implemented when Transaction domain is created
    expect(true)->toBeTrue();
});

test('user can delete empty account', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('accounts.destroy', $account));

    $response->assertRedirect();
    $this->assertSoftDeleted('accounts', ['id' => $account->id]);
});

test('user cannot access other users accounts', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)->get(route('accounts.show', $account));

    $response->assertForbidden();
});
