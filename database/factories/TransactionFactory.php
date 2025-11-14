<?php

namespace Database\Factories;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Transaction\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['income', 'expense', 'transfer']);

        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'category_id' => $type !== 'transfer' ? Category::factory() : null,
            'type' => $type,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'payee' => $this->faker->company(),
            'description' => $this->faker->optional()->sentence(),
            'date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'notes' => $this->faker->optional()->paragraph(),
            'related_transaction_id' => null,
        ];
    }

    /**
     * Indicate that the transaction is an income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'category_id' => Category::where('type', 'income')->inRandomOrder()->first()?->id,
        ]);
    }

    /**
     * Indicate that the transaction is an expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'category_id' => Category::where('type', 'expense')->inRandomOrder()->first()?->id,
        ]);
    }

    /**
     * Indicate that the transaction is a transfer.
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transfer',
            'category_id' => null,
            'payee' => 'Transfer',
        ]);
    }
}
