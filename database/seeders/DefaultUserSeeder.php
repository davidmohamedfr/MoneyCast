<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * WARNING: This creates an insecure default user for development only.
     * DO NOT run this seeder in production!
     */
    public function run(): void
    {
        // Only create if environment is local/development
        if (! app()->environment('local', 'development')) {
            $this->command->warn('Skipping default user creation - not in development environment');

            return;
        }

        // Check if user already exists
        if (User::where('email', 'user@example.com')->exists()) {
            $this->command->info('Default user already exists');

            return;
        }

        User::create([
            'name' => 'Dev User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('Default user created: user@example.com / password');
    }
}
