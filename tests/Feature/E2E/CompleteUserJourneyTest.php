<?php

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

test('complete new user onboarding journey', function () {
    seedCategories();

    // Step 1: User registers and logs in
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    actingAs($user);

    // Step 2: User lands on dashboard (empty state)
    $response = get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('accounts', 0)
        ->where('total_balance', 0)
    );

    // Step 3: User creates their first account (checking)
    $response = post(route('accounts.store'), [
        'name' => 'My Checking Account',
        'type' => 'checking',
        'bank' => 'Test Bank',
        'initial_balance' => 5000,
        'currency' => 'EUR',
    ]);
    $response->assertRedirect(route('accounts.index'));

    $checking = Account::where('user_id', $user->id)->first();

    // Step 4: User creates a savings account
    $response = post(route('accounts.store'), [
        'name' => 'Emergency Fund',
        'type' => 'savings',
        'bank' => 'Test Bank',
        'initial_balance' => 10000,
        'currency' => 'EUR',
    ]);
    $response->assertRedirect(route('accounts.index'));

    // Step 5: Dashboard now shows both accounts
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->has('accounts', 2)
        ->where('total_balance', 15000)
    );

    // Step 6: User records their first transaction (salary)
    $incomeCategory = Category::where('type', 'income')->first();

    $response = post(route('transactions.store'), [
        'account_id' => $checking->id,
        'type' => 'income',
        'amount' => 3500,
        'payee' => 'Monthly Salary',
        'date' => now()->format('Y-m-d'),
        'category_id' => $incomeCategory->id,
        'description' => 'September salary',
    ]);
    $response->assertRedirect(route('transactions.index'));
    $response->assertSessionHasNoErrors();

    // Step 7: User records some expenses
    $expenseCategory = Category::where('type', 'expense')->first();

    $response = post(route('transactions.store'), [
        'account_id' => $checking->id,
        'type' => 'expense',
        'amount' => 150,
        'payee' => 'Grocery Store',
        'date' => now()->format('Y-m-d'),
        'category_id' => $expenseCategory->id,
    ]);
    $response->assertRedirect(route('transactions.index'));
    $response->assertSessionHasNoErrors();

    $response = post(route('transactions.store'), [
        'account_id' => $checking->id,
        'type' => 'expense',
        'amount' => 80,
        'payee' => 'Gas Station',
        'date' => now()->format('Y-m-d'),
        'category_id' => $expenseCategory->id,
    ]);
    $response->assertRedirect(route('transactions.index'));
    $response->assertSessionHasNoErrors();

    // Step 8: Dashboard reflects all changes
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 18270) // 5000 + 10000 + 3500 - 150 - 80
        ->where('monthly_stats.income', 3500)
        ->where('monthly_stats.expenses', 230)
        ->where('monthly_stats.net', 3270)
        ->where('monthly_stats.transaction_count', 3)
        ->has('recent_transactions', 3)
    );

    // Step 9: User views account details
    $response = get(route('accounts.show', $checking));
    $response->assertInertia(fn ($page) => $page
        ->where('account.name', 'My Checking Account')
        ->where('stats.current_balance', 8270) // 5000 + 3500 - 150 - 80
    );

    // Verify final database state
    assertDatabaseCount('accounts', 2);
    assertDatabaseCount('transactions', 5); // 2 opening balance + 3 user transactions
});

test('complete expense tracking workflow', function () {
    seedCategories();

    $user = User::factory()->create();
    actingAs($user);

    // Setup: Create account and categories
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'initial_balance' => 2000,
    ]);

    $groceryCategory = Category::factory()->expense()->create(['name' => 'Groceries']);
    $utilityCategory = Category::factory()->expense()->create(['name' => 'Utilities']);
    $entertainmentCategory = Category::factory()->expense()->create(['name' => 'Entertainment']);

    // Week 1: Record groceries
    post(route('transactions.store'), [
        'account_id' => $account->id,
        'type' => 'expense',
        'amount' => 120,
        'payee' => 'Supermarket',
        'date' => now()->startOfMonth()->addDays(2)->format('Y-m-d'),
        'category_id' => $groceryCategory->id,
    ]);

    // Week 2: Pay utilities
    post(route('transactions.store'), [
        'account_id' => $account->id,
        'type' => 'expense',
        'amount' => 180,
        'payee' => 'Electric Company',
        'date' => now()->startOfMonth()->addDays(10)->format('Y-m-d'),
        'category_id' => $utilityCategory->id,
    ]);

    // Week 3: Entertainment
    post(route('transactions.store'), [
        'account_id' => $account->id,
        'type' => 'expense',
        'amount' => 50,
        'payee' => 'Cinema',
        'date' => now()->startOfMonth()->addDays(18)->format('Y-m-d'),
        'category_id' => $entertainmentCategory->id,
    ]);

    // Week 4: More groceries
    post(route('transactions.store'), [
        'account_id' => $account->id,
        'type' => 'expense',
        'amount' => 95,
        'payee' => 'Farmers Market',
        'date' => now()->startOfMonth()->addDays(25)->format('Y-m-d'),
        'category_id' => $groceryCategory->id,
    ]);

    // Check monthly stats
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->where('monthly_stats.expenses', 445)
        ->where('monthly_stats.transaction_count', 4)
    );

    // Check account balance
    $response = get(route('accounts.show', $account));
    $response->assertInertia(fn ($page) => $page
        ->where('stats.current_balance', 1880) // 2000 - 120 (only past transactions)
        // Note: projected_balance is not returned by show endpoint
    );
});

test('complete income and savings workflow', function () {
    seedCategories();

    $user = User::factory()->create();
    actingAs($user);

    // Create accounts
    $checking = Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'Checking',
        'initial_balance' => 500,
    ]);

    $savings = Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'Savings',
        'initial_balance' => 5000,
    ]);

    $salaryCategory = Category::factory()->income()->create(['name' => 'Salary']);

    // Receive salary
    post(route('transactions.store'), [
        'account_id' => $checking->id,
        'type' => 'income',
        'amount' => 4000,
        'payee' => 'Company Inc',
        'date' => now()->format('Y-m-d'),
        'category_id' => $salaryCategory->id,
        'description' => 'Monthly salary',
    ]);

    // Transfer to savings
    post(route('transactions.store'), [
        'account_id' => $checking->id,
        'type' => 'transfer',
        'amount' => 1000,
        'payee' => 'Transfer to Savings',
        'date' => now()->format('Y-m-d'),
    ]);

    // Pay some bills
    $billCategory = Category::factory()->expense()->create(['name' => 'Bills']);

    post(route('transactions.store'), [
        'account_id' => $checking->id,
        'type' => 'expense',
        'amount' => 800,
        'payee' => 'Rent',
        'date' => now()->format('Y-m-d'),
        'category_id' => $billCategory->id,
    ]);

    // Verify balances
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->where('monthly_stats.income', 4000)
        ->where('monthly_stats.expenses', 800)
    );

    // Checking: 500 + 4000 - 1000 - 800 = 2700
    $response = get(route('accounts.show', $checking));
    $response->assertInertia(fn ($page) => $page
        ->where('stats.current_balance', 2700)
    );
});

test('complete budget planning with future transactions', function () {
    seedCategories();

    $user = User::factory()->create();
    actingAs($user);

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'initial_balance' => 3000,
    ]);

    $category = Category::factory()->expense()->create();

    // Current transactions
    Transaction::factory()->expense()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 200,
        'date' => now()->format('Y-m-d'),
    ]);

    // Future planned expenses
    Transaction::factory()->expense()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 500,
        'payee' => 'Planned Payment',
        'date' => now()->addWeek()->format('Y-m-d'),
    ]);

    Transaction::factory()->expense()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 300,
        'payee' => 'Future Bill',
        'date' => now()->addWeeks(2)->format('Y-m-d'),
    ]);

    // Check account shows current balance
    $response = get(route('accounts.show', $account));
    $response->assertInertia(fn ($page) => $page
        ->where('stats.current_balance', 2800) // 3000 - 200
        // Note: projected_balance is not returned by show endpoint
    );
});

test('complete multi-account management workflow', function () {
    seedCategories();

    $user = User::factory()->create();
    actingAs($user);

    // Create diverse account portfolio
    $checking = Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'Everyday Checking',
        'type' => 'checking',
        'initial_balance' => 1500,
    ]);

    $savings = Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'Emergency Fund',
        'type' => 'savings',
        'initial_balance' => 8000,
    ]);

    $creditCard = Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'Credit Card',
        'type' => 'credit',
        'initial_balance' => -1200,
    ]);

    // Verify all accounts visible
    $response = get(route('accounts.index'));
    $response->assertInertia(fn ($page) => $page
        ->has('accounts', 3)
    );

    // Dashboard shows net worth
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 8300) // 1500 + 8000 - 1200
    );

    // Use credit card
    $expenseCategory = Category::factory()->expense()->create();

    post(route('transactions.store'), [
        'account_id' => $creditCard->id,
        'type' => 'expense',
        'amount' => 150,
        'payee' => 'Restaurant',
        'date' => now()->format('Y-m-d'),
        'category_id' => $expenseCategory->id,
    ]);

    // Pay off some credit card debt
    post(route('transactions.store'), [
        'account_id' => $creditCard->id,
        'type' => 'income',
        'amount' => 500,
        'payee' => 'Payment',
        'date' => now()->format('Y-m-d'),
    ]);

    // Verify updated balances
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->where('total_balance', 8650) // 1500 + 8000 + (-1200 - 150 + 500)
    );
});

test('complete error recovery workflow', function () {
    seedCategories();

    $user = User::factory()->create();
    actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->expense()->create();

    // User makes a mistake in transaction
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 999, // Wrong amount
        'payee' => 'Wrong Payee',
        'date' => now()->format('Y-m-d'),
    ]);

    // User realizes mistake and corrects it
    $response = get(route('transactions.edit', $transaction));
    $response->assertStatus(200);

    $response = put(route('transactions.update', $transaction), [
        'account_id' => $account->id,
        'type' => 'expense',
        'amount' => 100, // Corrected amount
        'payee' => 'Correct Payee',
        'date' => now()->format('Y-m-d'),
        'category_id' => $category->id,
    ]);

    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'amount' => '100.0000',
        'payee' => 'Correct Payee',
    ]);

    // User decides to delete the transaction
    $response = delete(route('transactions.destroy', $transaction));
    $response->assertRedirect(route('transactions.index'));

    assertDatabaseCount('transactions', 0);
});

test('complete data isolation verification', function () {
    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    // User 1 creates their data
    actingAs($user1);

    $user1Account = Account::factory()->create([
        'user_id' => $user1->id,
        'name' => 'User 1 Account',
        'initial_balance' => 1000,
    ]);

    Transaction::factory()->create([
        'user_id' => $user1->id,
        'account_id' => $user1Account->id,
        'amount' => 500,
    ]);

    // User 2 creates their data
    actingAs($user2);

    $user2Account = Account::factory()->create([
        'user_id' => $user2->id,
        'name' => 'User 2 Account',
        'initial_balance' => 2000,
    ]);

    Transaction::factory()->create([
        'user_id' => $user2->id,
        'account_id' => $user2Account->id,
        'amount' => 750,
    ]);

    // Verify User 2 only sees their data
    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->has('accounts', 1)
        ->has('recent_transactions', 1)
        ->where('accounts.0.account.name', 'User 2 Account')
    );

    // Verify User 1 only sees their data
    actingAs($user1);

    $response = get(route('dashboard'));
    $response->assertInertia(fn ($page) => $page
        ->has('accounts', 1)
        ->has('recent_transactions', 1)
        ->where('accounts.0.account.name', 'User 1 Account')
    );
});
