<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default corporate admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@corporate.com'],
            [
                'name' => 'Финансовый директор',
                'password' => Hash::make('password123'),
            ]
        );

        $this->call([
            TransactionSeeder::class,
        ]);
    }
}
