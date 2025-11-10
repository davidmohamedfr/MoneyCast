<?php

namespace Database\Factories;

use App\Domain\Account\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(2, true).' Account',
            'type' => $this->faker->randomElement(['checking', 'savings', 'credit']),
            'bank' => $this->faker->company(),
            'initial_balance' => 0, // Default to 0 to avoid opening balance transactions in tests
            'currency' => 'EUR',
        ];
    }
}
