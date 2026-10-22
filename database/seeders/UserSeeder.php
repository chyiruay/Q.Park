<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Two known local accounts, both with password "password".
 * admin@qpark.test is the only one that can reach /admin.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Admin', 'admin@qpark.test', '0111111111', true],
            ['Test User', 'user@qpark.test', '0122222222', false],
        ];

        foreach ($users as [$name, $email, $phone, $isAdmin]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone_number' => $phone,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->isAdmin = $isAdmin;  //not fillable, so set it directly
            $user->save();
        }
    }
}
