<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Seed categories for tests
 */
function seedCategories(): void
{
    $categories = [
        // Income categories
        ['name' => 'Salary', 'type' => 'income', 'icon' => 'briefcase'],
        ['name' => 'Freelance/Business', 'type' => 'income', 'icon' => 'laptop'],
        ['name' => 'Investments', 'type' => 'income', 'icon' => 'trending-up'],
        ['name' => 'Gifts', 'type' => 'income', 'icon' => 'gift'],
        ['name' => 'Other Income', 'type' => 'income', 'icon' => 'plus-circle'],

        // Expense categories
        ['name' => 'Groceries', 'type' => 'expense', 'icon' => 'shopping-cart'],
        ['name' => 'Rent/Mortgage', 'type' => 'expense', 'icon' => 'home'],
        ['name' => 'Utilities', 'type' => 'expense', 'icon' => 'zap'],
        ['name' => 'Transportation', 'type' => 'expense', 'icon' => 'car'],
        ['name' => 'Healthcare', 'type' => 'expense', 'icon' => 'heart'],
        ['name' => 'Entertainment', 'type' => 'expense', 'icon' => 'film'],
        ['name' => 'Shopping', 'type' => 'expense', 'icon' => 'shopping-bag'],
        ['name' => 'Dining Out', 'type' => 'expense', 'icon' => 'utensils'],
        ['name' => 'Insurance', 'type' => 'expense', 'icon' => 'shield'],
        ['name' => 'Other Expense', 'type' => 'expense', 'icon' => 'minus-circle'],
    ];

    foreach ($categories as $category) {
        \App\Domain\Category\Models\Category::create($category);
    }
}
