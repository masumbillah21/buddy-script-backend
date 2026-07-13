<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersData = [
            [
                'first_name' => 'Masum',
                'last_name' => 'Billah',
                'email' => 'mbillah21@gmail.com',
                'profile_image' => 'uploads/avatar1.png',
            ],
            [
                'first_name' => 'Dylan',
                'last_name' => 'Field',
                'email' => 'dylan@figma.com',
                'profile_image' => 'uploads/avatar2.png',
            ],
            [
                'first_name' => 'Steve',
                'last_name' => 'Jobs',
                'email' => 'steve@apple.com',
                'profile_image' => 'uploads/avatar3.png',
            ],
            [
                'first_name' => 'Ryan',
                'last_name' => 'Roslansky',
                'email' => 'ryan@linkedin.com',
                'profile_image' => 'uploads/avatar4.png',
            ],
            [
                'first_name' => 'Satya',
                'last_name' => 'Nadella',
                'email' => 'satya@microsoft.com',
                'profile_image' => 'uploads/avatar5.png',
            ],
        ];

        foreach ($usersData as $data) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                User::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'password' => Hash::make('12345678'),
                    'profile_image' => $data['profile_image'],
                ]);
            } else {
                $user->update([
                    'password' => Hash::make('12345678'),
                    'profile_image' => $data['profile_image'],
                ]);
            }
        }
    }
}
