<?php

namespace Database\Seeders;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException(
                'Cannot seed dev users in production environment. '.
                'This is a development-only feature for testing purposes.'
            );
        }

        if ($this->command && ! app()->runningUnitTests()) {
            if (! $this->command->confirm('This will create 5 dev users with various account and transaction scenarios. Continue?', true)) {
                $this->command->info('Dev user seeding cancelled.');

                return;
            }
        }

        if ($this->command) {
            $this->command->info('Creating dev users...');
            $this->command->newLine();
        }

        $categories = Category::all();
        $incomeCategories = $categories->where('type', 'income');
        $expenseCategories = $categories->where('type', 'expense');

        $devUsers = [];

        $devUsers[] = $this->createUserWithNoAccounts();

        $devUsers[] = $this->createUserWithSingleAccountNoTransactions();

        $devUsers[] = $this->createUserWithMultipleAccountsNoTransactions();

        $devUsers[] = $this->createUserWithSingleAccountAndTransactions($incomeCategories, $expenseCategories);

        $devUsers[] = $this->createUserWithMultipleAccountsAndTransactions($incomeCategories, $expenseCategories);

        if ($this->command) {
            $this->command->newLine();
            $this->command->info('Dev users created successfully!');
            $this->command->newLine();
            $this->command->info('You can now use the quick-login section on the login page to access these users.');
            $this->command->newLine();
        }
    }

    private function createUserWithNoAccounts(): array
    {
        $user = User::create([
            'name' => 'Dev User - No Accounts',
            'email' => 'dev-no-accounts@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        if ($this->command) {
            $this->command->info("✓ Created user: {$user->email} (No accounts)");
        }

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => 'No accounts',
        ];
    }

    private function createUserWithSingleAccountNoTransactions(): array
    {
        $user = User::create([
            'name' => 'Dev User - Single Account',
            'email' => 'dev-single-account@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Main Checking',
            'type' => 'checking',
            'bank' => 'Test Bank',
            'initial_balance' => 1500.00,
            'currency' => 'EUR',
        ]);

        if ($this->command) {
            $this->command->info("✓ Created user: {$user->email} (Single account, no transactions)");
        }

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => 'Single account, no transactions',
        ];
    }

    private function createUserWithMultipleAccountsNoTransactions(): array
    {
        $user = User::create([
            'name' => 'Dev User - Multiple Accounts',
            'email' => 'dev-multiple-accounts@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Primary Checking',
            'type' => 'checking',
            'bank' => 'Test Bank',
            'initial_balance' => 2500.00,
            'currency' => 'EUR',
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Savings Account',
            'type' => 'savings',
            'bank' => 'Test Bank',
            'initial_balance' => 8000.00,
            'currency' => 'EUR',
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Credit Card',
            'type' => 'credit',
            'bank' => 'Test Bank',
            'initial_balance' => -450.00,
            'currency' => 'EUR',
        ]);

        if ($this->command) {
            $this->command->info("✓ Created user: {$user->email} (3 accounts, no transactions)");
        }

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => '3 accounts, no transactions',
        ];
    }

    private function createUserWithSingleAccountAndTransactions($incomeCategories, $expenseCategories): array
    {
        $user = User::create([
            'name' => 'Dev User - Single Account + Transactions',
            'email' => 'dev-single-with-transactions@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Checking Account',
            'type' => 'checking',
            'bank' => 'Test Bank',
            'initial_balance' => 3000.00,
            'currency' => 'EUR',
        ]);

        $transactionCount = rand(8, 15);
        $this->createRandomTransactions($user, $account, $transactionCount, $incomeCategories, $expenseCategories);

        if ($this->command) {
            $this->command->info("✓ Created user: {$user->email} (Single account, {$transactionCount} transactions)");
        }

        return [
            'user' => $user,
            'email' => $user->email,
            'scenario' => "Single account, {$transactionCount} transactions",
        ];
    }

    private function createUserWithMultipleAccountsAndTransactions($incomeCategories, $expenseCategories): array
    {
        $user = User::create([
            'name' => 'Dev User - Multiple Accounts + Transactions',
            'email' => 'dev-multiple-with-transactions@moneycast.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $accounts = [
            Account::create([
                'user_id' => $user->id,
                'name' => 'Main Checking',
                'type' => 'checking',
                'bank' => 'Test Bank',
                'initial_balance' => 4500.00,
                'currency' => 'EUR',
            ]),
            Account::create([
                'user_id' => $user->id,
                'name' => 'Emergency Savings',
                'type' => 'savings',
                'bank' => 'Savings Bank',
                'initial_balance' => 12000.00,
                'currency' => 'EUR',
            ]),
            Account::create([
                'user_id' => $user->id,
                'name' => 'Visa Credit Card',
                'type' => 'credit',
                'bank' => 'Credit Bank',
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

        if ($this->command) {
            $this->command->info("✓ Created user: {$user->email} (3 accounts, {$totalTransactions} total transactions)");
        }

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
