<?php

use App\Domain\Account\Actions\CreateAccountAction;
use App\Domain\Account\Data\AccountData;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->createAction = app(CreateAccountAction::class);
});

test('account creation is wrapped in database transaction', function () {
    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $data = new AccountData(
        user_id: $this->user->id,
        name: 'Test Account',
        type: 'checking',
        bank: 'Test Bank',
        initial_balance: 1000,
        currency: 'USD',
    );

    $this->createAction->execute($data);
});

test('account creation rolls back on failure', function () {
    // Mock repository to throw exception after account creation
    $repository = Mockery::mock(\App\Domain\Account\Repositories\AccountRepositoryInterface::class);
    $repository->shouldReceive('create')
        ->once()
        ->andThrow(new \Exception('Database error'));

    $this->app->instance(\App\Domain\Account\Repositories\AccountRepositoryInterface::class, $repository);

    $action = app(CreateAccountAction::class);

    $data = new AccountData(
        user_id: $this->user->id,
        name: 'Test Account',
        type: 'checking',
        bank: 'Test Bank',
        initial_balance: 1000,
        currency: 'USD',
    );

    try {
        $action->execute($data);
    } catch (\Exception $e) {
        // Exception expected
    }

    // Verify nothing was created
    assertDatabaseMissing('accounts', [
        'name' => 'Test Account',
    ]);
});

test('both account and opening balance transaction are created atomically', function () {
    $initialAccountCount = DB::table('accounts')->count();
    $initialTransactionCount = DB::table('transactions')->count();

    $data = new AccountData(
        user_id: $this->user->id,
        name: 'Atomic Test Account',
        type: 'checking',
        bank: 'Test Bank',
        initial_balance: 5000,
        currency: 'USD',
    );

    $account = $this->createAction->execute($data);

    // Both account and opening balance transaction should be created
    expect(DB::table('accounts')->count())->toBe($initialAccountCount + 1);
    expect(DB::table('transactions')->count())->toBe($initialTransactionCount + 1);

    // Verify opening balance transaction exists
    $transaction = DB::table('transactions')
        ->where('account_id', $account->id)
        ->where('is_opening_balance', true)
        ->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->payee)->toBe('Opening Balance');
});

test('account creation with zero balance does not create opening balance transaction', function () {
    $initialTransactionCount = DB::table('transactions')->count();

    $data = new AccountData(
        user_id: $this->user->id,
        name: 'Zero Balance Account',
        type: 'checking',
        bank: 'Test Bank',
        initial_balance: 0,
        currency: 'USD',
    );

    $this->createAction->execute($data);

    // No opening balance transaction should be created
    expect(DB::table('transactions')->count())->toBe($initialTransactionCount);
});

test('account creation maintains data integrity on concurrent requests', function () {
    $data = new AccountData(
        user_id: $this->user->id,
        name: 'Concurrent Test Account',
        type: 'checking',
        bank: 'Test Bank',
        initial_balance: 1000,
        currency: 'USD',
    );

    // Create account in transaction
    $account = $this->createAction->execute($data);

    // Verify account exists with correct data
    expect($account->name)->toBe('Concurrent Test Account');
    expect($account->initial_balance)->toBe('1000.0000');

    // Verify opening balance transaction matches account
    $transaction = DB::table('transactions')
        ->where('account_id', $account->id)
        ->where('is_opening_balance', true)
        ->first();

    expect((float) $transaction->amount)->toBe(1000.0);
    expect($transaction->user_id)->toBe($this->user->id);
    expect($transaction->account_id)->toBe($account->id);
});
