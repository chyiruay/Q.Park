<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Two known local accounts, both with password "password".
 *
 * NOTE: there is no role column on users, so "admin" here is only a label —
 * the admin pages have no authorisation check and are reachable by anyone.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Admin', 'admin@qpark.test', '0111111111'],
            ['Test User', 'user@qpark.test', '0122222222'],
        ];

        foreach ($users as [$name, $email, $phone]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone_number' => $phone,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
