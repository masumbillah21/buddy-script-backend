<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('SEED_USER_EMAIL', 'test@example.com');

        if (!User::where('email', $email)->exists()) {
            User::create([
                'first_name' => env('SEED_USER_FIRST_NAME', 'Test'),
                'last_name' => env('SEED_USER_LAST_NAME', 'User'),
                'email' => $email,
                'password' => Hash::make(env('SEED_USER_PASSWORD', 'password')),
            ]);
        }
    }
}
