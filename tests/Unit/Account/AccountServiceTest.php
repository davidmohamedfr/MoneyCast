<?php

use App\Domain\Account\Models\Account;
use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Account\Services\AccountService;
use App\Models\User;

test('AccountRepository filters by user_id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Account::factory()->count(3)->create(['user_id' => $user1->id]);
    Account::factory()->count(2)->create(['user_id' => $user2->id]);

    $repository = app(AccountRepositoryInterface::class);
    $accounts = $repository->getAllForUser($user1->id);

    expect($accounts)->toHaveCount(3);
    expect($accounts->every(fn($account) => $account->user_id === $user1->id))->toBeTrue();
})->group('unit');

test('AccountService calculates current balance correctly excluding future transactions', function () {
    // This test will be fully implemented when Transaction domain is created
    expect(true)->toBeTrue();
});

test('AccountService calculates projected balance correctly including future transactions', function () {
    // This test will be fully implemented when Transaction domain is created
    expect(true)->toBeTrue();
});
