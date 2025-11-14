<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
            DB::table('categories')->insert([
                'name' => $category['name'],
                'type' => $category['type'],
                'icon' => $category['icon'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
