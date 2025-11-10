<?php

namespace Database\Seeders;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Models\DevMagicLink;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException(
                'Cannot seed test users in production environment. '.
                'This is a development-only feature for testing purposes.'
            );
        }

        if (!$this->command->confirm('This will create 5 test users with various account and transaction scenarios. Continue?', true)) {
            $this->command->info('Test user seeding cancelled.');
            return;
        }

        $this->command->info('Creating test users...');
        $this->command->newLine();

        $categories = Category::all();
        $incomeCategories = $categories->where('type', 'income');
        $expenseCategories = $categories->where('type', 'expense');

        $testUsers = [];

        $testUsers[] = $this->createUserWithNoAccounts();

        $testUsers[] = $this->createUserWithSingleAccountNoTransactions();

        $testUsers[] = $this->createUserWithMultipleAccountsNoTransactions();

        $testUsers[] = $this->createUserWithSingleAccountAndTransactions($incomeCategories, $expenseCategories);

        $testUsers[] = $this->createUserWithMultipleAccountsAndTransactions($incomeCategories, $expenseCategories);

        $this->command->newLine();
        $this->command->info('Test users created successfully!');
        $this->command->newLine();
        $this->command->info('Magic links for authentication:');
        $this->command->newLine();

        foreach ($testUsers as $userData) {
            $magicLink = DevMagicLink::generateForUser($userData['user'], 10080);
            $this->command->info("User: {$userData['email']}");
            $this->command->info("Scenario: {$userData['scenario']}");
            $this->command->info("Magic Link: {$magicLink->getUrl()}");
            $this->command->newLine();
        }
    }

    private function createUserWithNoAccounts(): array
    {
        $user = User::create([
            'name' => 'Test User - No Accounts',
            'email' => 'test-no-accounts@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->command->info("✓ Created user: {$user->email} (No accounts)");

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => 'No accounts',
        ];
    }

    private function createUserWithSingleAccountNoTransactions(): array
    {
        $user = User::create([
            'name' => 'Test User - Single Account',
            'email' => 'test-single-account@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Main Checking',
            'type' => 'checking',
            'initial_balance' => 1500.00,
            'currency' => 'EUR',
        ]);

        $this->command->info("✓ Created user: {$user->email} (Single account, no transactions)");

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => 'Single account, no transactions',
        ];
    }

    private function createUserWithMultipleAccountsNoTransactions(): array
    {
        $user = User::create([
            'name' => 'Test User - Multiple Accounts',
            'email' => 'test-multiple-accounts@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Primary Checking',
            'type' => 'checking',
            'initial_balance' => 2500.00,
            'currency' => 'EUR',
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Savings Account',
            'type' => 'savings',
            'initial_balance' => 8000.00,
            'currency' => 'EUR',
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Credit Card',
            'type' => 'credit_card',
            'initial_balance' => -450.00,
            'currency' => 'EUR',
        ]);

        $this->command->info("✓ Created user: {$user->email} (3 accounts, no transactions)");

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => '3 accounts, no transactions',
        ];
    }

    private function createUserWithSingleAccountAndTransactions($incomeCategories, $expenseCategories): array
    {
        $user = User::create([
            'name' => 'Test User - Single Account + Transactions',
            'email' => 'test-single-with-transactions@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Checking Account',
            'type' => 'checking',
            'initial_balance' => 3000.00,
            'currency' => 'EUR',
        ]);

        $transactionCount = rand(8, 15);
        $this->createRandomTransactions($user, $account, $transactionCount, $incomeCategories, $expenseCategories);

        $this->command->info("✓ Created user: {$user->email} (Single account, {$transactionCount} transactions)");

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => "Single account, {$transactionCount} transactions",
        ];
    }

    private function createUserWithMultipleAccountsAndTransactions($incomeCategories, $expenseCategories): array
    {
        $user = User::create([
            'name' => 'Test User - Multiple Accounts + Transactions',
            'email' => 'test-multiple-with-transactions@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $accounts = [
            Account::create([
                'user_id' => $user->id,
                'name' => 'Main Checking',
                'type' => 'checking',
                'initial_balance' => 4500.00,
                'currency' => 'EUR',
            ]),
            Account::create([
                'user_id' => $user->id,
                'name' => 'Emergency Savings',
                'type' => 'savings',
                'initial_balance' => 12000.00,
                'currency' => 'EUR',
            ]),
            Account::create([
                'user_id' => $user->id,
                'name' => 'Visa Credit Card',
                'type' => 'credit_card',
                'initial_balance' => -850.00,
                'currency' => 'EUR',
            ]),
        ];

        $totalTransactions = 0;
        foreach ($accounts as $account) {
            $transactionCount = rand(8, 15);
            $this->createRandomTransactions($user, $account, $transactionCount, $incomeCategories, $expenseCategories);
            $totalTransactions += $transactionCount;
        }

        $this->command->info("✓ Created user: {$user->email} (3 accounts, {$totalTransactions} total transactions)");

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => "3 accounts, {$totalTransactions} total transactions",
        ];
    }

    private function createRandomTransactions(User $user, Account $account, int $count, $incomeCategories, $expenseCategories): void
    {
        $startDate = now()->subMonths(3);
        $endDate = now();

        $payees = [
            'income' => ['Employer Corp', 'Freelance Client', 'Consulting LLC', 'Side Gig Inc'],
            'expense' => [
                'Amazon', 'Grocery Store', 'Gas Station', 'Restaurant',
                'Netflix', 'Spotify', 'Electric Company', 'Water Utility',
                'Internet Provider', 'Coffee Shop', 'Pharmacy', 'Department Store',
            ],
        ];

        for ($i = 0; $i < $count; $i++) {
            $type = rand(0, 100) < 30 ? 'income' : 'expense';
            $category = $type === 'income'
                ? $incomeCategories->random()
                : $expenseCategories->random();

            $amount = $type === 'income'
                ? rand(500, 3000) / 10
                : rand(10, 500) / 10;

            $payee = $payees[$type][array_rand($payees[$type])];

            $randomDays = rand(0, 90);
            $date = $startDate->copy()->addDays($randomDays);

            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $account->id,
                'category_id' => $category->id,
                'type' => $type,
                'amount' => $amount,
                'payee' => $payee,
                'description' => "Test {$type} transaction",
                'date' => $date->format('Y-m-d'),
                'notes' => null,
            ]);
        }
    }
}
