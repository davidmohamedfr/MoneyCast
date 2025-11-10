<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DefaultUserSeeder::class,
            CategorySeeder::class,
        ]);

        if (!app()->environment('production')) {
            $this->call([
                DevUsersSeeder::class,
            ]);
        }
    }
}
